<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductItem>
 */
class ProductItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'order_id' => null,
            'content' => fake()->userName() . ':' . fake()->password(8, 16),
            'status' => 'available',
            'reserved_at' => null,
            'sold_at' => null,
        ];
    }

    public function reserved(): static
    {
        return $this->state(fn () => [
            'status' => 'reserved',
            'reserved_at' => now(),
        ]);
    }

    public function sold(): static
    {
        return $this->state(fn () => [
            'status' => 'sold',
            'sold_at' => now(),
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn () => [
            'product_id' => $product->id,
        ]);
    }
}
