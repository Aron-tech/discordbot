<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

if (! function_exists('getGuildData')) {
    /**
     * @throws ConnectionException
     */
    function getGuildData(string $guild_id, ?string $data_type = null): array
    {
        $bot_token = config('services.discord.bot_token');

        $url = "https://discord.com/api/v10/guilds/{$guild_id}";
        if ($data_type !== null) {
            $url .= '/'.ltrim($data_type, '/');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bot '.$bot_token,
        ])->get($url);

        return $response->successful() ? $response->json() : [];
    }
}

if (! function_exists('getMemberData')) {
    /**
     * @throws ConnectionException
     */
    function getMemberData(string $guild_id, string $discord_id): array
    {
        $bot_token = config('services.discord.bot_token');

        $response = Http::withHeaders([
            'Authorization' => 'Bot '.$bot_token,
        ])->get("https://discord.com/api/v10/guilds/{$guild_id}/members/{$discord_id}");

        if ($response->successful()) {
            return $response->json();
        } else {
            return [];
        }
    }
}

if (! function_exists('changeMemberRole')) {
    function changeMemberData(string $guild_id, string $discord_id, array $new_roles)
    {
        $bot_token = config('services.discord.bot_token');

        return Http::withHeaders([
            'Authorization' => 'Bot '.$bot_token,
            'Content-Type' => 'application/json',
        ])->patch("https://discord.com/api/guilds/{$guild_id}/members/{$discord_id}", [
            'roles' => array_values($new_roles),
        ]);
    }
}

if (! function_exists('getChannelValue')) {
    function getChannelValue($model, $key, $default = null)
    {
        return data_get($model->channels, $key, $default);
    }
}

if (! function_exists('getRoleValue')) {
    function getRoleValue($model, $key, $default = null): string|array|null
    {
        return data_get($model->roles, $key, $default);
    }
}

if (! function_exists('getSettingValue')) {
    function getSettingValue($model, $key, $default = null): int|string|array|null
    {
        return data_get($model->settings, $key, $default);
    }
}

if (! function_exists('dutyTimeFormatter')) {
    function dutyTimeFormatter(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $remaining_minutes = $minutes % 60;

        return sprintf('%d:%02d', $hours, $remaining_minutes);
    }
}
