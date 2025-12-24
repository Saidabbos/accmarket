<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => [
                'required',
                'integer',
                'min:' . config('shop.cart.min_quantity_per_item'),
                'max:' . config('shop.cart.max_quantity_per_item'),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $minQty = config('shop.cart.min_quantity_per_item');
        $maxQty = config('shop.cart.max_quantity_per_item');

        return [
            'product_id.required' => 'Product is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be a number.',
            'quantity.min' => "Quantity must be at least {$minQty}.",
            'quantity.max' => "Quantity cannot exceed {$maxQty}.",
        ];
    }
}
