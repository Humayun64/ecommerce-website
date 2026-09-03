<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->is_admin;
    }

    protected function prepareForValidation(): void
    {
        // Empty number and select inputs arrive as "" — MySQL rejects that
        // in numeric and foreign-key columns.
        $this->merge([
            'slug'                => $this->filled('slug') ? $this->slug : null,
            'category_id'         => $this->filled('category_id') ? $this->category_id : null,
            'brand_id'            => $this->filled('brand_id') ? $this->brand_id : null,
            'compare_price'       => $this->filled('compare_price') ? $this->compare_price : null,
            'cost_price'          => $this->filled('cost_price') ? $this->cost_price : null,
            'stock'               => $this->filled('stock') ? $this->stock : 0,
            'low_stock_threshold' => $this->filled('low_stock_threshold') ? $this->low_stock_threshold : 5,
        ]);
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'name'                => ['required', 'string', 'max:200'],
            'slug'                => ['nullable', 'string', 'max:220', Rule::unique('products')->ignore($product)],
            'sku'                 => ['required', 'string', 'max:60', Rule::unique('products')->ignore($product)],
            'category_id'         => ['nullable', 'exists:categories,id'],
            'brand_id'            => ['nullable', 'exists:brands,id'],
            'type'                => ['required', Rule::in(['physical', 'digital'])],
            'origin'              => ['nullable', 'string', 'max:80'],
            'size_label'          => ['nullable', 'string', 'max:80'],
            'short_description'   => ['nullable', 'string', 'max:400'],
            'description'         => ['nullable', 'string', 'max:20000'],

            'price'               => ['required', 'numeric', 'min:0'],
            'compare_price'       => ['nullable', 'numeric', 'min:0', 'gt:price'],
            'cost_price'          => ['nullable', 'numeric', 'min:0'],
            'stock'               => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],

            'attribute_ids'            => ['array', 'max:2'],
            'attribute_ids.*'          => ['nullable', 'exists:attributes,id'],

            'variants'                 => ['array'],
            'variants.*.value_ids'     => ['array'],
            'variants.*.value_ids.*'   => ['nullable', 'exists:attribute_values,id'],
            'variants.*.id'            => ['nullable', 'integer'],
            'variants.*.name'          => ['nullable', 'string', 'max:120'],
            'variants.*.sku'           => ['nullable', 'string', 'max:60'],
            'variants.*.price'         => ['nullable', 'numeric', 'min:0'],
            'variants.*.compare_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.cost_price'    => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock'         => ['nullable', 'integer', 'min:0'],

            'images'   => ['array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],

            'meta_title'       => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'og_title'         => ['nullable', 'string', 'max:95'],
            'og_description'   => ['nullable', 'string', 'max:200'],
            'canonical_url'    => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'compare_price.gt'      => 'The old price must be higher than the selling price, or leave it empty.',
            'images.*.max'          => 'Each image must be under 6 MB.',
            'meta_title.max'        => 'Keep the meta title under 70 characters or Google will cut it off.',
            'meta_description.max'  => 'Keep the meta description under 180 characters or Google will cut it off.',
        ];
    }
}
