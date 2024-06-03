<?php

namespace App\Infrastructure\CreateUser;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsCreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'El username es obligatorio',
            'username.string' => 'El username debe ser una cadena de texto',
            'password.required' => 'La password es obligatoria',
            'password.string' => 'La password debe ser una cadena de texto',
        ];
    }
}
