<?php

namespace App\Infrastructure\GetTopsOfTheTops;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsTopsOfTheTopsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'since' => 'nullable|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'since.integer' => 'El atributo since debe ser un entero',
            'since.min' => 'El atributo since debe ser como mínimo 1',
        ];
    }
}
