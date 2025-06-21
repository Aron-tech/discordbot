<?php

namespace App\Console\Commands;

use App\Models\Guild;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncDiscordGuilds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-discord-guilds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bot_token = config('services.discord.bot_token');

        $response = Http::withHeaders([
            'Authorization' => 'Bot '.$bot_token,
        ])->get('https://discord.com/api/v10/users/@me/guilds');

        if ($response->failed()) {
            $this->error('Sikertelen kapcsolódás a Discord API-hoz.');
            return;
        }

        $guilds = $response->json();

        $discord_guild_ids = collect($guilds)->pluck('id')->toArray();

        Guild::query()
            ->whereNotIn('guild_id', $discord_guild_ids)
            ->delete();

        Log::info('Megmaradt guild ID-k: ', $discord_guild_ids);

        $this->info('Sikeresen frissítve.');
    }
}
