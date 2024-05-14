<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsFollowRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'userId' => 'required|string',
            'streamerId' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'userId.required' => 'El campo userId es obligatorio.',
            'userId.string' => 'El campo userId debe ser una cadena de caracteres.',
            'streamerId.required' => 'El campo streamerId es obligatorio.',
            'streamerId.string' => 'El campo streamerId debe ser una cadena de caracteres.',
        ];
    }
}
