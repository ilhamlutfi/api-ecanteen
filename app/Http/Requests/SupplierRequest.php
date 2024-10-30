<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return auth()->check();
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $routeId = $this->route('supplier');

        return [
            'code' => 'required|digits:3|unique:suppliers,code,'. $routeId . ',uuid',
            'name' => 'required|string|min:3|max:255',
            'address' => 'required|string|min:3|max:255',
            'phone' => 'required|numeric',
            'email' => 'required|email',
        ];
    }
}
