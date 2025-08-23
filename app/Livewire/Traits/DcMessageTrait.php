<?php

namespace App\Livewire\Traits;

use App\Enums\Guild\ChannelTypeEnum;
use App\Models\Guild;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait DcMessageTrait
{
    /**
     * Küld egy alapértelmezett log üzenetet Discordra.
     *
     * @param  string  $channel_id  Discord csatorna ID
     * @param array{
     *     command?: string,
     *     user?: string,
     *     target_user?: string,
     *     message?: string
     * } $options
     * @return array<string, mixed>|null
     */
    public function sendDefaultLog(string $channel_id, array $options): ?array
    {
        $command = $options['command'] ?? null;
        $command = $command ? $command.' Parancs' : 'Weboldal';
        $embed = [
            'title' => '📝 '.$command.' Log',
            'color' => hexdec('0099ff'),
            'footer' => [
                'text' => 'Duty Management System • Elküldve: '.now()->locale('hu')->translatedFormat('Y.m.d H:i:s'),
            ],
            'fields' => [
                [
                    'name' => '👤 Felhasználó',
                    'value' => $options['user'] ? '<@'.$options['user'].'>' : 'Ismeretlen',
                    'inline' => true,
                ],
            ],
        ];

        if (! empty($options['target_user'])) {
            $embed['fields'][] = [
                'name' => '🎯 Cél felhasználó',
                'value' => $options['target_user'],
                'inline' => true,
            ];
        }

        if (! empty($options['message'])) {
            $embed['fields'][] = [
                'name' => '📄 Üzenet',
                'value' => $options['message'],
                'inline' => false,
            ];
        }

        try {
            return $this->sendEmbed($channel_id, $embed);
        } catch (\Throwable $e) {
            Log::error('Error sending duty log to Discord: '.$e->getMessage());

            return null;
        }
    }

    private function sendEmbed(string $channel_id, array $embed): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bot '.config('services.discord.bot_token'),
                'Content-Type' => 'application/json',
            ])->post("https://discord.com/api/v10/channels/{$channel_id}/messages", [
                'embeds' => [$embed],
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending embed to Discord: '.$e->getMessage());
            throw $e;
        }

        return $response->json();
    }

    /**
     * Küld egy duty log szöveges üzenetet Discordra.
     *
     * @param array{
     *     command: string,
     *     user: string,
     *     target_user?: string,
     *     message?: string
     * } $options
     * @return array<string, mixed>|null
     */
    public function sendDutyLog(string $channel_id, array $options): ?array
    {
        $command = $options['command'] ?? null;
        $command = $command ? $command.' Parancs' : 'Weboldal';

        $message = '📝 **'.$command." Log**\n";
        $message .= '👤 **Felhasználó:** '.($options['user'] ? '<@'.$options['user'].'>' : 'Ismeretlen')."\n";

        if (! empty($options['target_user'])) {
            $message .= '🎯 **Cél felhasználó:** <@'.$options['target_user'].">\n";
        }

        if (! empty($options['message'])) {
            $message .= '📄 **Üzenet:** '.$options['message']."\n";
        }

        $message .= '⏰ **Időpont:** '.now()->locale('hu')->translatedFormat('Y.m.d H:i:s');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bot '.config('services.discord.bot_token'),
                'Content-Type' => 'application/json',
            ])->post("https://discord.com/api/v10/channels/{$channel_id}/messages", [
                'content' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending duty log to Discord: '.$e->getMessage());

            return null;
        }

        return $response->json();
    }

    public function getDefaultLogChannelId(Guild $guild): ?string
    {
        return getChannelValue($guild, ChannelTypeEnum::DEFAULT_LOG->value);
    }

    public function getDutyLogChannelId(Guild $guild): ?string
    {
        return getChannelValue($guild, ChannelTypeEnum::DUTY_LOG->value);
    }
}
