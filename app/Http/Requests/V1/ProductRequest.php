<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => [$this->isMethod('POST') ? 'required' : 'sometimes', 'required', 'string', 'max:255'], 'category_id' => [$this->isMethod('POST') ? 'required' : 'sometimes', 'required', 'integer', 'exists:categories,id'], 'unit_price' => [$this->isMethod('POST') ? 'required' : 'sometimes', 'required', 'regex:/^[0-9]{1,6}(\.[0-9]{1,2})?$/'], 'description' => ['sometimes', 'nullable', 'string', 'max:10000'], 'unit' => ['sometimes', 'required', 'string', 'max:255'], 'image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'remove_image' => ['sometimes', 'boolean']];
    }
}
