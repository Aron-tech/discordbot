<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotStatus extends Model
{
    protected $fillable = ['last_ping_at'];

    protected $casts = [
        'last_ping_at' => 'datetime',
    ];
}
