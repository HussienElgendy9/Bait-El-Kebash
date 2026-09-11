<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'], 'password' => ['required', 'string', 'max:255', 'confirmed', Password::defaults()], 'phone_number' => ['required', 'string', 'regex:/^01[0-9]{9}$/', 'unique:users'], 'address' => ['required', 'string', 'max:1000']];
    }
}
