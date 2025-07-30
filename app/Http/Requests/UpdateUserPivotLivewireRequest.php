<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPivotLivewireRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ic_name' => ['required', 'string', 'min:3', 'max:50'],
            'ic_number' => ['required', 'string', 'min:2', 'max:4'],
            'ic_tel' => ['nullable', 'string', 'min:3', 'max:13'],
        ];
    }
}
