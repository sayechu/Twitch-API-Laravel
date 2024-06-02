<?php

namespace App\Infrastructure\FollowStreamer;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsFollowStreamerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'streamerId' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'El username es obligatorio',
            'username.string' => 'El username debe ser una cadena de texto',
            'streamerId.required' => 'El streamerId es obligatorio',
            'streamerId.numeric' => 'El streamerId debe ser un número',
        ];
    }
}
