<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlackListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'min:3', 'max:255'],
        ];
    }
}
