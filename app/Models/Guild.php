<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guild extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'guild_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'guild_id',
        'name',
        'roles',
        'installed',
        'channels',
        'settings',
    ];

    protected $casts = [
        'roles' => 'json',
        'channels' => 'json',
        'settings' => 'json',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guild_user', 'guild_guild_id', 'user_discord_id')
            ->withPivot('ic_name', 'ic_number', 'ic_tel', 'last_role_time', 'last_warn_time', 'created_at', 'freedom_expiring');
    }

    public function usersWithOutPivot(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function duties(): HasMany
    {
        return $this->hasMany(Duty::class);
    }

    public function dutiesWithTrashed(): HasMany
    {
        return $this->hasMany(Duty::class)->withTrashed();
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function blacklists(): HasMany
    {
        return $this->hasMany(BlackList::class);
    }

    public function blacklistsWithTrashed(): HasMany
    {
        return $this->hasMany(BlackList::class)->withTrashed();
    }
}
