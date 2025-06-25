<?php

namespace App\Http\Controllers\Api;

use App\Enums\Guild\ChannelTypeEnum;
use App\Enums\Guild\RoleTypeEnum;
use App\Enums\Guild\SettingTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuildRequest;
use App\Models\Guild;
use Illuminate\Http\JsonResponse;

class GuildController extends Controller
{
    public function store(GuildRequest $request): JsonResponse
    {
        $validated = $request->validated();

        Guild::updateOrCreate(
            ['guild_id' => $validated['guild_id']],
            ['name' => $validated['name']]
        );

        return response()->json(['message' => 'Guild sikeresen elmentve.'], 200);
    }

    public function get(Guild $guild): JsonResponse
    {
        $roles = [];
        foreach (RoleTypeEnum::cases() as $role_type) {
            $roles[$role_type->value] = getRoleValue($guild, $role_type->value);
        }

        $channels = [];
        foreach (ChannelTypeEnum::cases() as $channel_type) {
            $channels[$channel_type->value] = getChannelValue($guild, $channel_type->value);
        }

        $settings = [];
        foreach (SettingTypeEnum::cases() as $setting_type) {
            $settings[$setting_type->value] = getSettingValue($guild, $setting_type->value);
        }

        return response()->json([
            'message' => 'Guild sikeresen lekérdezve.',
            'installed' => $guild->installed,
            ...$roles,
            ...$channels,
            ...$settings,
        ], 200);
    }

    public function update(GuildRequest $request, Guild $guild): JsonResponse
    {
        $validated = $request->validated();

        $guild->update([
            'name' => $validated['name'] ?? $guild->name,
            'installed' => $validated['installed'] ?? $guild->installed,
        ]);

        return response()->json(['message' => 'Guild sikeresen frissítve.'], 200);
    }

    public function getGuildList(): JsonResponse
    {
        $guild_ids = Guild::where('installed', true)->pluck('guild_id');

        return response()->json([
            'message' => 'Guildok id-je sikeresen lekérdezve.',
            'guild_ids' => $guild_ids,
        ], 200);
    }

    public function install(Guild $guild): JsonResponse
    {
        $is_empty_column = false;

        foreach (SettingTypeEnum::cases() as $setting_type) {
            $setting_value = getSettingValue($guild, $setting_type->value);
            if ($setting_value === null) {
                $is_empty_column = true;
                break;
            }
        }

        foreach (ChannelTypeEnum::cases() as $channel_type) {
            $channel_value = getChannelValue($guild, $channel_type->value);
            if ($channel_value === null) {
                $is_empty_column = true;
                break;
            }
        }

        foreach (RoleTypeEnum::cases() as $role_type) {
            $role_value = getRoleValue($guild, $role_type->value);
            if ($role_value === null) {
                $is_empty_column = true;
                break;
            }
        }

        if ($is_empty_column) {
            return response()->json([
                'message' => 'A guild telepítése sikertelen, mert hiányoznak a szükséges beállítások.',
                'installed' => false,
            ], 400);
        }

        $guild->update([
            'installed' => true,
        ]);

        return response()->json([
            'message' => 'Guild sikeresen telepítve.',
        ], 200);
    }
}
