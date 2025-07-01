<?php

namespace App\Http\Controllers\Api;

use App\Enums\Guild\ChannelTypeEnum;
use App\Enums\Guild\RoleTypeEnum;
use App\Enums\Guild\SettingTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuildRequest;
use App\Models\Guild;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function getExpiredUserStates(Guild $guild): JsonResponse
    {
        $expired_warned_users = $guild->users()
            ->wherePivot('last_warn_time', '<', now()->addDays(getSettingValue($guild, SettingTypeEnum::WARN_TIME->value, 7)))
            ->pluck('discord_id');

        $expired_holiday_users = $guild->users()
            ->wherePivot('freedom_expiring', '<', now())
            ->pluck('discord_id');

        return response()->json([
            'message' => 'Sikeresen lekérdezted a lejárt szabadságok és lejárt figyelmeztetéssel rendelkező felhasználókat.',
            'expired_warned_users' => $expired_warned_users,
            'expired_holiday_users' => $expired_holiday_users,
            'log_channel' => getChannelValue($guild, ChannelTypeEnum::DEFAULT_LOG->value),
            'holiday_role' => getRoleValue($guild, RoleTypeEnum::FREEDOM_ROLE->value),
            'warn_roles' => getRoleValue($guild, RoleTypeEnum::WARN_ROLES->value),
        ], 200);
    }

    public function updateExpiredUserStates(Request $request, Guild $guild): JsonResponse
    {
        $validated = $request->validate([
            'expired_warned_users' => 'array',
            'expired_warned_users.*' => 'array',
            'expired_holiday_users' => 'array',
            'expired_holiday_users.*' => 'string',
        ]);

        if (! empty($validated['expired_warned_users'])) {
            foreach ($validated['expired_warned_users'] as $user) {
                $discord_id = is_array($user) ? $user['id'] : $user;
                $escalated = is_array($user) ? ($user['escalated'] ?? false) : false;

                $new_date = $escalated
                    ? now()->addDays(getSettingValue($guild, SettingTypeEnum::WARN_TIME->value, 7))
                    : null;

                $guild->users()->where('discord_id', $discord_id)
                    ->update(['guild_user.last_warn_time' => $new_date]);
            }
        }

        if (! empty($validated['expired_holiday_users'])) {
            $guild->users()->whereIn('discord_id', $validated['expired_holiday_users'])
                ->update(['guild_user.freedom_expiring' => null]);
        }

        return response()->json(['message' => 'Expired user states updated.'], 200);
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
