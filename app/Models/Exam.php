<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $fillable = [
        'guild_guild_id',
        'name',
        'min_pass_score',
        'attempt_count',
        'minute_per_task',
        'q_number',
        'visible',
    ];

    protected $casts = [
        'visible' => 'boolean',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    public function bestResultForAuthUser()
    {
        return $this->hasOne(ExamResult::class)
            ->where('user_discord_id', auth()->id())
            ->orderByDesc('score');
    }
}
