<?php

namespace App\Http\Controllers;

use App\Models\BotStatus;
use Carbon\Carbon;
use Illuminate\Http\Response;

class BotStatusController extends Controller
{
    public function index(): Response
    {
        $last_ping = BotStatus::select('last_ping_at')->first()?->last_ping_at;

        if (! $last_ping || Carbon::parse($last_ping)->diffInMinutes(now()) > 3) {
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
