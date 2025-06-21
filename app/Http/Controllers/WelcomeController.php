<?php

namespace App\Http\Controllers;

use App\Models\Guild;
use App\Models\User;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index(){
        $guild_count = Guild::count();
        $user_count = User::count();
        return view('welcome', compact('guild_count','user_count'));
    }
}
