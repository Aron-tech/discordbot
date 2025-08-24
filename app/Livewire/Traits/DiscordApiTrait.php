<?php

namespace App\Livewire\Traits;

use Exception;
use Illuminate\Support\Facades\Http;

trait DiscordApiTrait
{
    protected string $discord_base_url = 'https://discord.com/api/v10';

    /**
     * Új kategória létrehozása Discordon
     *
     * @throws Exception
     */
    public function createDiscordCategory(string $guild_id, string $name, ?string $bot_token = null): array
    {
        $token = $bot_token ?? config('services.discord.bot_token');

        $response = Http::withHeaders([
            'Authorization' => 'Bot '.$token,
            'Content-Type' => 'application/json',
        ])->post(
            "{$this->discord_base_url}/guilds/{$guild_id}/channels",
            [
                'name' => $name,
                'type' => 4, // 4 = CATEGORY
            ]
        );

        if ($response->failed()) {
            throw new Exception('Discord API error: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Új csatorna létrehozása kategóriába
     *
     * @throws Exception
     */
    public function createDiscordChannel(string $guild_id, string $name, string $category_id, ?string $bot_token = null): array
    {
        $token = $bot_token ?? config('services.discord.bot_token');

        $response = Http::withHeaders([
            'Authorization' => 'Bot '.$token,
            'Content-Type' => 'application/json',
        ])->post(
            "{$this->discordBaseUrl}/guilds/{$guild_id}/channels",
            [
                'name' => $name,
                'type' => 0, // 0 = TEXT
                'parent_id' => $category_id,
            ]
        );

        if ($response->failed()) {
            throw new Exception('Discord API error: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Meglévő kategória frissítése Discordon
     *
     * @throws Exception
     */
    public function updateDiscordCategory(string $guild_id, string $category_id, array $data, ?string $bot_token = null): array
    {
        $token = $bot_token ?? config('services.discord.bot_token');

        $response = Http::withHeaders([
            'Authorization' => 'Bot '.$token,
            'Content-Type' => 'application/json',
        ])->patch(
            "{$this->discord_base_url}/channels/{$category_id}",
            $data
        );

        if ($response->failed()) {
            throw new Exception('Discord API error (update category): '.$response->body());
        }

        return $response->json();
    }

    public function createDiscordCategoryWithTicketPermissions(
        string $guild_id,
        string $name,
        array $role_ids,
        ?string $bot_token = null
    ): array {
        $token = $bot_token ?? config('services.discord.bot_token');

        $permissionsMap = [
            'VIEW_CHANNEL' => 1 << 10,          // 1024
            'SEND_MESSAGES' => 1 << 11,        // 2048
            'USE_APPLICATION_COMMANDS' => 1 << 31, // 2097152
            'MENTION_EVERYONE' => 1 << 3,
            'ADD_REACTIONS' => 1 << 6,
            'USE_EXTERNAL_APPS' => 1 << 50,
        ];

        $allow = array_sum($permissionsMap);

        $permission_overwrites = [];

        $permission_overwrites[] = [
            'id' => $guild_id, // @everyone role has the same id as the guild
            'type' => 0,
            'allow' => '0',
            'deny' => (string) $permissionsMap['VIEW_CHANNEL'],
        ];

        foreach ($role_ids as $roleId) {
            $permission_overwrites[] = [
                'id' => $roleId,
                'type' => 0, // 0 = role
                'allow' => (string) $allow,
                'deny' => '0',
            ];
        }

        $payload = [
            'name' => $name,
            'type' => 4, // CATEGORY
            'permission_overwrites' => $permission_overwrites,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bot '.$token,
            'Content-Type' => 'application/json',
        ])->post("{$this->discord_base_url}/guilds/{$guild_id}/channels", $payload);

        if ($response->failed()) {
            throw new \Exception('Discord API error: '.$response->body());
        }

        return $response->json();
    }
}
