<?php

namespace App\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'El Id es obligatorio',
            'id.numeric' => 'El Id dado no es válido',
        ];
    }
}
