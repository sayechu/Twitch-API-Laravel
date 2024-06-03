<?php

namespace App\Infrastructure\Timeline;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsTimelineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'El username es obligatorio',
            'username.string' => 'El username debe ser una cadena de texto'
        ];
    }
}
