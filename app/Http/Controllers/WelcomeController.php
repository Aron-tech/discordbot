<?php

namespace App\Http\Controllers;

use App\Models\Guild;
use App\Models\User;

class WelcomeController extends Controller
{
    public function index()
    {
        $guild_count = cache()->remember('guild_count', 30, function () {
            return Guild::count();
        });
        $user_count = cache()->remember('user_count', 30, function () {
            return User::count();
        });

        return view('welcome', compact('guild_count', 'user_count'));
    }
}
