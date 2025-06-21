<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('discord')->scopes(['identify', 'email', 'guilds'])
            ->redirect();
    }
    public function callback()
    {
        $discord_user = Socialite::driver('discord')->user();

        $user = User::updateOrCreate(
            ['discord_id' => $discord_user->id],
            [
                'discord_id' => $discord_user->id,
                'name' => $discord_user->name,
                'avatar' => $discord_user->avatar,
                'email' => $discord_user->email,
                'd_token' => $discord_user->token,
                'd_refresh_token' => $discord_user->refreshToken,
            ]
        );

        Auth::login($user);

        return to_route('guild.selector');

    }

    public function logout()
    {
        Auth::logout();

        return to_route('welcome');
    }
}
