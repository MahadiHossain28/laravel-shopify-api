<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateShopifyProductRequest extends FormRequest
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
            'title' => 'required|string',
            'description' => 'required|string',
            'vendor' => 'required|string',
            'product_type' => 'required|string',
            'status' => 'required|string|in:active,draft,archived',
            'options' => 'required|array',
            'options.*.name' => 'required|string',
            'options.*.values' => 'required|array',
            'variants' => 'required|array',
            'variants.*.options' => 'required|array',
            'variants.*.price' => 'required|numeric',
            'variants.*.sku' => 'required|string',
            'variants.*.inventory_quantity' => 'required|integer',
            'images' => 'array',
            'images.*.src' => 'required|url',
            'images.*.alt' => 'required|string',
        ];
    }
}
