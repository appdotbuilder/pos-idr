<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['stock_in', 'stock_out', 'adjustment']);
        $quantity = fake()->numberBetween(1, 50);
        $previousStock = fake()->numberBetween(0, 100);
        
        $newStock = match($type) {
            'stock_in' => $previousStock + $quantity,
            'stock_out' => max(0, $previousStock - $quantity),
            'adjustment' => fake()->numberBetween(0, 100),
            default => $previousStock,
        };
        
        return [
            'product_id' => Product::factory(),
            'type' => $type,
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'reference_type' => fake()->optional(0.7)->randomElement(['sale', 'purchase', 'adjustment']),
            'reference_id' => fake()->optional(0.7)->numberBetween(1, 1000),
            'notes' => fake()->optional(0.5)->sentence(),
            'user_id' => User::factory(),
        ];
    }
}