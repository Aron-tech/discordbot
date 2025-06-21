<?php

namespace App\Gates;

use App\Enums\Guild\RoleTypeEnum;
use App\Enums\PermissionEnum;
use App\Models\Guild;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PermissionGate
{
    public static function register()
    {
        Gate::define('hasPermission', callback: function (User $user, Guild $guild, PermissionEnum $permission) {
            $admin_roles = getRoleValue($guild, RoleTypeEnum::ADMIN_ROLES->value);
            $mod_roles = getRoleValue($guild, RoleTypeEnum::MOD_ROLES->value);
            $default_roles = getRoleValue($guild, RoleTypeEnum::DEFAULT_ROLES->value);

            $member_data = Cache::remember("member_data_{$guild->guild_id}_{$user->discord_id}", now()->addHours(2), function () use ($guild, $user) {
                return getMemberData($guild->guild_id, $user->discord_id);
            });

            $owner_id = Cache::remember("guild_owner_{$guild->guild_id}", now()->addDays(7), function () use ($guild) {
                $guild_data = getGuildData($guild->guild_id);
                Log::info("Guild data for {$guild->guild_id}: ".json_encode($guild_data));

                return $guild_data['owner_id'] ?? null;
            });

            Log::info("Checking permissions for user {$user->discord_id} in guild {$guild->guild_id} for permission {$permission->value}. {$owner_id}");

            if ($user->discord_id === $owner_id) {
                return true;
            }

            $user_roles = $member_data['roles'] ?? [];

            if (! empty(array_intersect($user_roles, $admin_roles))) {
                return true; // Admin has all permissions
            }

            if (! empty(array_intersect($user_roles, $mod_roles))) {
                return in_array($permission, PermissionEnum::modPermissions());
            }

            if (! empty(array_intersect($user_roles, $default_roles))) {
                return in_array($permission, PermissionEnum::defaultPermissions());
            }

            return false;
        });
    }
}
