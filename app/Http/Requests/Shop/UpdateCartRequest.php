<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => [
                'required',
                'integer',
                'min:0',
                'max:' . config('shop.cart.max_quantity_per_item'),
            ],
        ];
    }

    public function messages(): array
    {
        $maxQty = config('shop.cart.max_quantity_per_item');

        return [
            'product_id.required' => 'Product is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be a number.',
            'quantity.min' => 'Quantity cannot be negative.',
            'quantity.max' => "Quantity cannot exceed {$maxQty}.",
        ];
    }
}
