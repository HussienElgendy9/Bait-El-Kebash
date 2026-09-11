<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['sometimes', 'required', 'string', 'max:255'], 'email' => ['sometimes', 'required', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)], 'address' => ['sometimes', 'required', 'string', 'max:1000']];
    }
}
