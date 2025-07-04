<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $primaryKey = 'discord_id';
    protected $keyType = 'string';
    public $incrementing = false;


    protected $fillable = [
        'discord_id',
        'name',
        'email',
        'avatar',
        'd_token',
        'd_refresh_token',
        'is_dev',
    ];

    protected $casts = [
        'is_dev' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    public function guilds(): BelongsToMany
    {
        return $this->belongsToMany(Guild::class);
    }

    public function duties(): HasMany
    {
        return $this->hasMany(Duty::class);
    }

    public function blacklists(): HasMany
    {
        return $this->hasMany(BlackList::class);
    }

    public function dutiesWithTrashed(): HasMany
    {
        return $this->hasMany(Duty::class)->withTrashed();
    }

    public function periodDutyTime(Guild $guild): int
    {
        return $this->duties()->where('guild_guild_id', $guild->guild_id)->sum('value');
    }

    public function totalDutyTime(Guild $guild): int
    {
        return $this->dutiesWithTrashed()->where('guild_guild_id', $guild->guild_id)->sum('value');
    }

    public function exam_results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }
}
