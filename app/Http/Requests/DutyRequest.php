<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DutyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string'],
            'duty_time' => ['nullable', 'integer'],
            'is_executing' => ['nullable'],
        ];
    }
}
