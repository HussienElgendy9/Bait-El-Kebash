<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class PhoneRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['phone_number' => ['required', 'string', 'regex:/^01[0-9]{9}$/', 'unique:users,phone_number']];
    }
}
