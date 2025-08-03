<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PosController extends Controller
{
    /**
     * Display the POS interface.
     */
    public function index()
    {
        $products = Product::active()->with(['inventoryMovements' => function ($query) {
            $query->latest()->take(5);
        }])->get();
        
        $customers = Customer::active()->get();
        $promotions = Promotion::valid()->get();
        $lowStockProducts = Product::lowStock()->active()->count();
        
        // Recent sales for dashboard
        $recentSales = Sale::with(['customer', 'user', 'saleItems.product'])
            ->latest()
            ->take(5)
            ->get();
        
        // Today's statistics
        $todayStats = [
            'sales_count' => Sale::whereDate('created_at', today())->completed()->count(),
            'sales_total' => Sale::whereDate('created_at', today())->completed()->sum('total_amount'),
            'customers_served' => Sale::whereDate('created_at', today())->completed()->distinct('customer_id')->count('customer_id'),
        ];

        return Inertia::render('pos/index', [
            'products' => $products,
            'customers' => $customers,
            'promotions' => $promotions,
            'lowStockProducts' => $lowStockProducts,
            'recentSales' => $recentSales,
            'todayStats' => $todayStats,
        ]);
    }

    /**
     * Process a sale transaction.
     */
    public function store(ProcessSaleRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            
            // Create sale record
            $sale = Sale::create([
                'transaction_number' => Sale::generateTransactionNumber(),
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => auth()->id(),
                'subtotal' => $data['subtotal'],
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'total_amount' => $data['total_amount'],
                'payment_method' => $data['payment_method'],
                'amount_paid' => $data['amount_paid'],
                'change_amount' => $data['change_amount'] ?? 0,
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            // Create sale items and update stock
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Check stock availability
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total_price' => $item['total_price'],
                ]);

                // Update product stock
                $product->updateStock(
                    $item['quantity'],
                    'stock_out',
                    'sale',
                    $sale->id,
                    "Sale: {$sale->transaction_number}",
                    auth()->id()
                );
            }

            // Award loyalty points if customer exists
            if ($sale->customer_id) {
                $customer = Customer::find($sale->customer_id);
                $loyaltyPoints = (int) floor($sale->total_amount / 10000); // 1 point per IDR 10,000
                $customer->addLoyaltyPoints($loyaltyPoints);
            }

            // Use promotion if applied
            if (!empty($data['promotion_code'])) {
                $promotion = Promotion::where('code', $data['promotion_code'])->valid()->first();
                if ($promotion) {
                    $promotion->use();
                }
            }

            DB::commit();

            return Inertia::render('pos/receipt', [
                'sale' => $sale->load(['customer', 'user', 'saleItems.product']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display sale receipt.
     */
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'saleItems.product']);
        
        return Inertia::render('pos/receipt', [
            'sale' => $sale,
        ]);
    }
}