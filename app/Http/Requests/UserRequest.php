<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'discord_id' => ['required'],
            'name' => ['required'], //
            'email' => ['nullable'], //
            'avatar' => ['nullable'],
        ];
    }
}
