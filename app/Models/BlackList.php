<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlackList extends Model
{
    protected $fillable = [
        'user_discord_id',
        'guild_guild_id',
        'reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}
