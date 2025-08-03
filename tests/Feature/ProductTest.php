<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);
    }

    public function test_can_view_products_index(): void
    {
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('products/index'));
    }

    public function test_can_create_product(): void
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST123',
            'description' => 'A test product',
            'price' => 25000,
            'stock_quantity' => 50,
            'low_stock_threshold' => 10,
            'category' => 'Test Category',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)->post('/products', $productData);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST123',
            'price' => 25000,
        ]);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'stock_quantity' => 10,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'sku' => $product->sku,
            'price' => $product->price,
            'stock_quantity' => 20, // Increased stock
            'low_stock_threshold' => $product->low_stock_threshold,
            'category' => $product->category,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->put("/products/{$product->id}", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'stock_quantity' => 20,
        ]);

        // Check inventory movement was created (stock increased from 10 to 20, difference is 10)
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'stock_in',
            'quantity' => 10, // Difference: 20 - 10 = 10
        ]);
    }

    public function test_product_shows_low_stock_status(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
        ]);

        $this->assertTrue($product->is_low_stock);
    }
}