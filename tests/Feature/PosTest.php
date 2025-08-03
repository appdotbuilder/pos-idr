<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);
    }

    public function test_can_view_pos_interface(): void
    {
        $response = $this->actingAs($this->user)->get('/pos');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('pos/index'));
    }

    public function test_can_process_sale(): void
    {
        $product = Product::factory()->create([
            'price' => 50000,
            'stock_quantity' => 10,
        ]);

        $customer = Customer::factory()->create();

        $saleData = [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => $product->price,
                    'discount_amount' => 0,
                    'total_price' => $product->price * 2,
                ]
            ],
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 10000,
            'total_amount' => 110000,
            'payment_method' => 'cash',
            'amount_paid' => 120000,
            'change_amount' => 10000,
        ];

        $response = $this->actingAs($this->user)->post('/pos', $saleData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('sales', [
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'total_amount' => 110000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        // Check stock was updated
        $product->refresh();
        $this->assertEquals(8, $product->stock_quantity);
    }

    public function test_cannot_process_sale_with_insufficient_stock(): void
    {
        $product = Product::factory()->create([
            'price' => 50000,
            'stock_quantity' => 1,
        ]);

        $saleData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5, // More than available stock
                    'unit_price' => $product->price,
                    'discount_amount' => 0,
                    'total_price' => $product->price * 5,
                ]
            ],
            'subtotal' => 250000,
            'discount_amount' => 0,
            'tax_amount' => 25000,
            'total_amount' => 275000,
            'payment_method' => 'cash',
            'amount_paid' => 275000,
            'change_amount' => 0,
        ];

        $response = $this->actingAs($this->user)->post('/pos', $saleData);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('sales', [
            'total_amount' => 275000,
        ]);
    }
}