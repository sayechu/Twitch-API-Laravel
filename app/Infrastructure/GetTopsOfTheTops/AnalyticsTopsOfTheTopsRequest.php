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
}
