<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class PhoneVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['verification_id' => ['required', 'integer', 'min:1'], 'code' => ['required', 'string', 'regex:/^[0-9]{6}$/']];
    }
}
