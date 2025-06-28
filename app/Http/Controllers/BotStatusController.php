<?php

namespace App\Http\Controllers;

use App\Models\BotStatus;
use Illuminate\Http\Response;

class BotStatusController extends Controller
{
    public function index(): Response
    {
        $last_ping = BotStatus::select('last_ping_at')->first();

        if (! $last_ping || $last_ping->diffInMinutes(now()) > 5) {
            return response('Bot offline', 503);
        }

        return response('Bot online', 200);
    }

    public function store(): Response
    {
        BotStatus::updateOrCreate(
            ['id' => 1],
            ['last_ping_at' => now()]
        );
        return response('Bot status updated', 200);
    }
}
