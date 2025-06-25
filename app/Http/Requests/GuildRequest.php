<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuildRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'guild_id' => ['required', 'string'],
            'name' => ['required', 'string', 'min:3', 'max:50'],
            'installed' => ['nullable', 'boolean'],
        ];
    }
}
