<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('inventoryMovements')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%");
            })
            ->when(request('category'), function ($query, $category) {
                $query->where('category', $category);
            })
            ->when(request('low_stock'), function ($query) {
                $query->lowStock();
            })
            ->latest()
            ->paginate(20);

        $categories = Product::distinct()->pluck('category')->filter()->sort()->values();

        return Inertia::render('products/index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => request()->only(['search', 'category', 'low_stock']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Product::distinct()->pluck('category')->filter()->sort()->values();
        
        return Inertia::render('products/create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        // Create initial stock movement if stock is provided
        if ($product->stock_quantity > 0) {
            $product->updateStock(
                $product->stock_quantity,
                'stock_in',
                'adjustment',
                null,
                'Initial stock',
                auth()->id()
            );
        }

        return redirect()->route('products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['inventoryMovements.user']);
        
        return Inertia::render('products/show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Product::distinct()->pluck('category')->filter()->sort()->values();
        
        return Inertia::render('products/edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $originalStock = $product->stock_quantity;
        $data = $request->validated();
        $newStock = $data['stock_quantity'] ?? $originalStock;
        
        // Update product without changing stock first
        $stockData = $data;
        $stockData['stock_quantity'] = $originalStock; // Keep original stock
        $product->update($stockData);

        // Create stock adjustment if stock quantity changed
        if ($newStock !== $originalStock) {
            $difference = $newStock - $originalStock;
            $type = $difference > 0 ? 'stock_in' : 'stock_out';
            
            $product->updateStock(
                abs($difference),
                $type,
                'adjustment',
                null,
                'Stock adjustment via product update',
                auth()->id()
            );
        }

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Soft delete by marking as inactive instead of actual deletion
        $product->update(['is_active' => false]);

        return redirect()->route('products.index')
            ->with('success', 'Product deactivated successfully.');
    }
}