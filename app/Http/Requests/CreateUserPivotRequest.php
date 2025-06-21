<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserPivotRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ic_name' => ['required', 'min:3', 'max:50'], //
            'ic_number' => ['required', 'min:2', 'max:4'], //
            'ic_tel' => ['nullable', 'min:3', 'max:13'],
        ];
    }
}
