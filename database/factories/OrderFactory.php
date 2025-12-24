<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 1, 100);

        return [
            'buyer_id' => User::factory(),
            'product_id' => Product::factory(),
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $quantity * $unitPrice,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_id' => null,
            'payment_method' => null,
            'paid_at' => null,
            'completed_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'payment_status' => 'completed',
            'payment_id' => 'PAY-' . strtoupper(uniqid()),
            'payment_method' => 'crypto',
            'paid_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'payment_status' => 'completed',
            'payment_id' => 'PAY-' . strtoupper(uniqid()),
            'payment_method' => 'crypto',
            'paid_at' => now()->subHour(),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
        ]);
    }

    public function forBuyer(User $buyer): static
    {
        return $this->state(fn () => [
            'buyer_id' => $buyer->id,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn () => [
            'product_id' => $product->id,
            'unit_price' => $product->price,
        ]);
    }
}
