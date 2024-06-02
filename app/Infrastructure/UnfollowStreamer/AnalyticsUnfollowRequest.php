<?php

namespace App\Infrastructure\UnfollowStreamer;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsUnfollowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'streamerId' => 'required|integer'
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'El nombre de usuario es obligatorio',
            'username.string' => 'El nombre de usuario debe ser una cadena de caracteres',
            'streamerId.required' => 'El ID del streamer es obligatorio',
            'streamerId.integer' => 'El ID del streamer debe ser un número entero',
        ];
    }
}
