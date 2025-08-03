<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(50000, 1000000);
        $discountAmount = fake()->numberBetween(0, $subtotal * 0.2);
        $taxAmount = ($subtotal - $discountAmount) * 0.1;
        $totalAmount = $subtotal - $discountAmount + $taxAmount;
        
        return [
            'transaction_number' => 'TXN-' . fake()->date('Ymd') . '-' . fake()->numberBetween(1000, 9999),
            'customer_id' => fake()->optional(0.7)->randomElement(Customer::pluck('id')->toArray()),
            'user_id' => User::factory(),
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'payment_method' => fake()->randomElement(['cash', 'card', 'digital']),
            'amount_paid' => $totalAmount + fake()->numberBetween(0, 50000),
            'change_amount' => fake()->numberBetween(0, 50000),
            'status' => fake()->randomElement(['completed', 'pending', 'cancelled']),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the sale is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}