<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class PaginationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['page' => ['sometimes', 'integer', 'min:1'], 'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'], 'category_id' => ['sometimes', 'integer', 'exists:categories,id'], 'q' => ['sometimes', 'string', 'max:255'], 'status' => ['sometimes', 'in:pending,completed,cancelled'], 'role' => ['sometimes', 'in:admin,customer'], 'unread' => ['sometimes', 'boolean'], 'sort' => ['sometimes', 'in:newest,price_asc,price_desc']];
    }
}
