<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');
        return $product && $product->seller_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:' . config('shop.product.max_name_length'),
            'description' => 'nullable|string|max:' . config('shop.product.max_description_length'),
            'price' => [
                'required',
                'numeric',
                'min:' . config('shop.product.min_price'),
                'max:' . config('shop.product.max_price'),
            ],
            'category_id' => 'required|exists:categories,id',
            'items_file' => [
                'nullable',
                'file',
                'mimes:' . config('shop.file_upload.allowed_mimes'),
                'max:' . config('shop.file_upload.max_size_kb'),
            ],
        ];
    }

    public function messages(): array
    {
        $maxNameLength = config('shop.product.max_name_length');
        $maxDescLength = config('shop.product.max_description_length');
        $minPrice = config('shop.product.min_price');
        $maxPrice = number_format(config('shop.product.max_price'), 2);
        $maxFileSize = config('shop.file_upload.max_size_mb');

        return [
            'name.required' => 'Product name is required.',
            'name.max' => "Product name cannot exceed {$maxNameLength} characters.",
            'description.max' => "Description cannot exceed {$maxDescLength} characters.",
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => "Price must be at least \${$minPrice}.",
            'price.max' => "Price cannot exceed \${$maxPrice}.",
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'The selected category does not exist.',
            'items_file.file' => 'Items must be a file.',
            'items_file.mimes' => 'Items file must be csv, txt, json, xlsx, or xls.',
            'items_file.max' => "Items file cannot exceed {$maxFileSize}MB.",
        ];
    }
}
