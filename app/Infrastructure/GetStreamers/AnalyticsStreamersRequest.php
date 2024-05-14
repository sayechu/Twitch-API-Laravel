<?php

namespace App\Infrastructure\GetStreamers;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsStreamersRequest extends FormRequest
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
