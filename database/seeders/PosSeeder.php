<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create users with different roles
        $admin = User::create([
            'name' => 'POS Admin',
            'email' => 'admin@pos.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $manager = User::create([
            'name' => 'Store Manager',
            'email' => 'manager@pos.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $cashier = User::create([
            'name' => 'Cashier',
            'email' => 'cashier@pos.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create sample products
        $products = [
            [
                'name' => 'Smartphone Samsung Galaxy',
                'sku' => 'SM001',
                'description' => 'Latest Samsung Galaxy smartphone with advanced features',
                'price' => 3500000,
                'stock_quantity' => 25,
                'low_stock_threshold' => 5,
                'category' => 'Electronics',
                'is_active' => true,
            ],
            [
                'name' => 'Laptop ASUS VivoBook',
                'sku' => 'LP001',
                'description' => 'Lightweight laptop perfect for work and entertainment',
                'price' => 7500000,
                'stock_quantity' => 15,
                'low_stock_threshold' => 3,
                'category' => 'Electronics',
                'is_active' => true,
            ],
            [
                'name' => 'Coffee Arabica Premium',
                'sku' => 'CF001',
                'description' => 'Premium arabica coffee beans, freshly roasted',
                'price' => 85000,
                'stock_quantity' => 50,
                'low_stock_threshold' => 10,
                'category' => 'Food & Beverage',
                'is_active' => true,
            ],
            [
                'name' => 'T-Shirt Cotton Basic',
                'sku' => 'TS001',
                'description' => 'Comfortable 100% cotton t-shirt in various colors',
                'price' => 75000,
                'stock_quantity' => 3, // Low stock
                'low_stock_threshold' => 10,
                'category' => 'Clothing',
                'is_active' => true,
            ],
            [
                'name' => 'Wireless Mouse Logitech',
                'sku' => 'MS001',
                'description' => 'Ergonomic wireless mouse with long battery life',
                'price' => 125000,
                'stock_quantity' => 40,
                'low_stock_threshold' => 8,
                'category' => 'Electronics',
                'is_active' => true,
            ],
            [
                'name' => 'Notebook A5 Lined',
                'sku' => 'NB001',
                'description' => 'High-quality lined notebook perfect for notes',
                'price' => 25000,
                'stock_quantity' => 2, // Low stock
                'low_stock_threshold' => 15,
                'category' => 'Books',
                'is_active' => true,
            ],
            [
                'name' => 'Hand Sanitizer 100ml',
                'sku' => 'HS001',
                'description' => 'Antibacterial hand sanitizer with 70% alcohol',
                'price' => 15000,
                'stock_quantity' => 100,
                'low_stock_threshold' => 20,
                'category' => 'Health & Beauty',
                'is_active' => true,
            ],
            [
                'name' => 'Sports Water Bottle',
                'sku' => 'WB001',
                'description' => 'BPA-free sports water bottle with leak-proof cap',
                'price' => 45000,
                'stock_quantity' => 30,
                'low_stock_threshold' => 8,
                'category' => 'Sports',
                'is_active' => true,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);
            
            // Create initial inventory movement for products with stock > 0
            if ($product->stock_quantity > 0) {
                $product->inventoryMovements()->create([
                    'type' => 'stock_in',
                    'quantity' => $product->stock_quantity,
                    'previous_stock' => 0,
                    'new_stock' => $product->stock_quantity,
                    'reference_type' => 'adjustment',
                    'reference_id' => null,
                    'notes' => 'Initial stock',
                    'user_id' => $admin->id,
                ]);
            }
        }

        // Create additional random products
        Product::factory(30)->create();

        // Create sample customers
        $customers = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@email.com',
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No. 123, Jakarta',
                'loyalty_points' => 150,
                'status' => 'active',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@email.com',
                'phone' => '081987654321',
                'address' => 'Jl. Sudirman No. 456, Bandung',
                'loyalty_points' => 250,
                'status' => 'active',
            ],
            [
                'name' => 'Ahmad Rahman',
                'email' => 'ahmad@email.com',
                'phone' => '081122334455',
                'address' => 'Jl. Diponegoro No. 789, Surabaya',
                'loyalty_points' => 75,
                'status' => 'active',
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }

        // Create additional random customers
        Customer::factory(20)->active()->create();

        // Create sample promotions
        $promotions = [
            [
                'name' => 'New Year Sale',
                'code' => 'NEWYEAR2024',
                'description' => '15% discount for all electronics',
                'type' => 'percentage',
                'value' => 15,
                'minimum_purchase' => 100000,
                'usage_limit' => 100,
                'usage_count' => 0,
                'start_date' => now()->subDays(7),
                'end_date' => now()->addDays(30),
                'is_active' => true,
            ],
            [
                'name' => 'Flash Sale',
                'code' => 'FLASH50',
                'description' => 'IDR 50,000 off for purchases above IDR 200,000',
                'type' => 'fixed_amount',
                'value' => 50000,
                'minimum_purchase' => 200000,
                'usage_limit' => 50,
                'usage_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addDays(7),
                'is_active' => true,
            ],
            [
                'name' => 'Customer Appreciation',
                'code' => 'THANKS10',
                'description' => '10% discount for loyal customers',
                'type' => 'percentage',
                'value' => 10,
                'minimum_purchase' => null,
                'usage_limit' => null,
                'usage_count' => 0,
                'start_date' => now()->subDays(3),
                'end_date' => now()->addDays(60),
                'is_active' => true,
            ],
        ];

        foreach ($promotions as $promotionData) {
            Promotion::create($promotionData);
        }

        // Create additional random promotions
        Promotion::factory(5)->valid()->create();
    }
}