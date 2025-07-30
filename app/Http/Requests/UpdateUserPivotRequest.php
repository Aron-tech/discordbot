<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPivotRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ic_name' => ['nullable', 'min:3', 'max:50'],
            'ic_number' => ['nullable', 'min:2', 'max:4'],
            'ic_tel' => ['nullable', 'min:3', 'max:13'],
            'last_role_time' => ['nullable'],
            'last_warn_time' => ['nullable'],
            'freedom_expiring' => ['nullable', 'date'],
        ];
    }
}
