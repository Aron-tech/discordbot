<?php

namespace App\Gates;

use App\Enums\Guild\RoleTypeEnum;
use App\Enums\PermissionEnum;
use App\Models\Guild;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class PermissionGate
{
    public static function register()
    {
        Gate::define('hasPermission', callback: function (User $user, Guild $guild, PermissionEnum $permission) {
            $admin_roles = getRoleValue($guild, RoleTypeEnum::ADMIN_ROLES->value) ?? [];
            $mod_roles = getRoleValue($guild, RoleTypeEnum::MOD_ROLES->value) ?? [];
            $default_roles = getRoleValue($guild, RoleTypeEnum::DEFAULT_ROLES->value) ?? [];

            $member_data = Cache::remember("member_data_{$guild->guild_id}_{$user->discord_id}", now()->addHours(2), function () use ($guild, $user) {
                return getMemberData($guild->guild_id, $user->discord_id);
            });

            $owner_id = Cache::remember("guild_owner_{$guild->guild_id}", now()->addDays(7), function () use ($guild) {
                $guild_data = getGuildData($guild->guild_id);

                return $guild_data['owner_id'] ?? null;
            });

            if ($user->discord_id === $owner_id) {
                return true;
            }

            if (self::hasAdministratorPermission($guild, $member_data['roles'] ?? [])) {
                return true;
            }

            $user_roles = $member_data['roles'] ?? [];

            if (! empty(array_intersect($user_roles, $admin_roles))) {
                return true;
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

    protected static function hasAdministratorPermission(Guild $guild, array $user_roles): bool
    {
        $guild_roles = Cache::remember("guild_roles_{$guild->guild_id}", now()->addDays(7), function () use ($guild) {
            $guild_data = getGuildData($guild->guild_id);

            return collect($guild_data['roles'] ?? []);
        });

        return $guild_roles
            ->filter(fn ($role) => in_array($role['id'], $user_roles))
            ->some(fn ($role) => ($role['permissions'] & (1 << 3)) !== 0); // 1 << 3 == 8 (ADMINISTRATOR)
    }
}
