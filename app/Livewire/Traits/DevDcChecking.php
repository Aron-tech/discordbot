<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait DevDcChecking
{
    public function isDevMember(string $discord_user_id): bool
    {
        $bot_token = config('services.discord.bot_token');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bot '.$bot_token,
            ])->get("https://discord.com/api/v10/guilds/1394218179554967583/members/{$discord_user_id}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Discord API hiba: '.$e->getMessage());

            return false;
        }
    }
}
