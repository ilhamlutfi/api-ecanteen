<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $routeId = $this->route('product');

        return [
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'name' => 'required|string|min:3|max:255|unique:products,name,'. $routeId . ',uuid',
            'image' => $this->method() === 'POST' ? 'required|file|image|mimes:png,jpg,jpeg,webp|mimetypes:image/png,image/jpg,image/jpeg,image/webp|max:2048' : 'nullable|file|image|mimes:png,jpg,jpeg,webp|mimetypes:image/png,image/jpg,image/jpeg,image/webp|max:2048',
            'price' => 'required|integer|numeric',
            'quantity' => 'required|numeric',
            'description' => 'nullable|string|min:3|max:255',
        ];
    }
}
