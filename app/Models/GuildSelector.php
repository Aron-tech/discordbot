<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class GuildSelector extends Model
{
    public const SESSION_KEY = 'selected_guild';

    public static function setGuild(Guild $guild)
    {
        Session::put(self::SESSION_KEY, $guild->guild_id);

        return to_route('dashboard');
    }

    public static function getGuild(): ?Guild
    {
        return once(function () {
            $guild_id = Session::get(self::SESSION_KEY);

            return $guild_id ? Guild::find($guild_id) : null;
        });
    }

    public static function clearGuild(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::save();
    }

    public static function hasGuild(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

}
