<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['percentage', 'fixed_amount']);
        
        return [
            'name' => fake()->words(3, true) . ' Promotion',
            'code' => fake()->unique()->regexify('[A-Z]{4}[0-9]{2}'),
            'description' => fake()->sentence(),
            'type' => $type,
            'value' => $type === 'percentage' 
                ? fake()->numberBetween(5, 50) // 5-50% discount
                : fake()->numberBetween(10000, 100000), // IDR 10k-100k discount
            'minimum_purchase' => fake()->optional(0.7)->numberBetween(50000, 500000), // IDR 50k-500k
            'usage_limit' => fake()->optional(0.6)->numberBetween(10, 100),
            'usage_count' => 0,
            'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+2 months'),
            'is_active' => fake()->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Indicate that the promotion is currently valid.
     */
    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'start_date' => fake()->dateTimeBetween('-1 week', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+1 month'),
        ]);
    }
}