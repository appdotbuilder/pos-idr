<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Display sales reports.
     */
    public function index()
    {
        $startDate = request('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = request('end_date', now()->format('Y-m-d'));

        // Sales overview
        $salesData = Sale::completed()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as transaction_count,
                SUM(total_amount) as total_sales,
                AVG(total_amount) as average_sale
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top selling products
        $topProducts = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->selectRaw('
                products.name,
                products.sku,
                SUM(sale_items.quantity) as total_quantity,
                SUM(sale_items.total_price) as total_revenue
            ')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_quantity')
            ->take(10)
            ->get();

        // Payment method breakdown
        $paymentMethods = Sale::completed()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                payment_method,
                COUNT(*) as transaction_count,
                SUM(total_amount) as total_amount
            ')
            ->groupBy('payment_method')
            ->get();

        // Summary statistics
        $summary = Sale::completed()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_transaction,
                SUM(discount_amount) as total_discounts
            ')
            ->first();

        return Inertia::render('reports/index', [
            'salesData' => $salesData,
            'topProducts' => $topProducts,
            'paymentMethods' => $paymentMethods,
            'summary' => $summary,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $type = 'inventory')
    {
        $products = Product::with(['inventoryMovements' => function ($query) {
                $query->latest()->take(5);
            }])
            ->when(request('low_stock_only'), function ($query) {
                $query->lowStock();
            })
            ->when(request('category'), function ($query, $category) {
                $query->where('category', $category);
            })
            ->get();

        $categories = Product::distinct()->pluck('category')->filter()->sort()->values();
        
        $lowStockCount = Product::lowStock()->active()->count();
        $outOfStockCount = Product::where('stock_quantity', 0)->active()->count();
        $totalProducts = Product::active()->count();
        $totalStockValue = Product::active()->selectRaw('SUM(stock_quantity * price) as total')->value('total');

        return Inertia::render('reports/inventory', [
            'products' => $products,
            'categories' => $categories,
            'stats' => [
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'total_products' => $totalProducts,
                'total_stock_value' => $totalStockValue,
            ],
            'filters' => request()->only(['low_stock_only', 'category']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $type = 'customers')
    {
        $startDate = request('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = request('end_date', now()->format('Y-m-d'));

        $customers = Customer::with(['sales' => function ($query) use ($startDate, $endDate) {
                $query->completed()->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['sales as total_purchases' => function ($query) use ($startDate, $endDate) {
                $query->completed()->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withSum(['sales as total_spent' => function ($query) use ($startDate, $endDate) {
                $query->completed()->whereBetween('created_at', [$startDate, $endDate]);
            }], 'total_amount')
            ->orderByDesc('total_spent')
            ->take(50)
            ->get();

        $customerStats = [
            'total_customers' => Customer::active()->count(),
            'new_customers' => Customer::whereBetween('created_at', [$startDate, $endDate])->count(),
            'repeat_customers' => Customer::has('sales', '>', 1)->count(),
            'total_loyalty_points' => Customer::sum('loyalty_points'),
        ];

        return Inertia::render('reports/customers', [
            'customers' => $customers,
            'customerStats' => $customerStats,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }
}