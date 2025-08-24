<?php

namespace App\Actions;

use App\Enums\Guild\SettingTypeEnum;
use App\Models\Guild;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckingDutyAction
{
    use AsAction;

    public function handle(Guild $guild, bool $is_executing = false): Collection
    {

        $next_checking_time = getSettingValue($guild, SettingTypeEnum::NEXT_CHECKING_TIME->value);
        $min_rank_up_duty = getSettingValue($guild, SettingTypeEnum::MIN_RANK_UP_DUTY->value);
        $min_rank_up_time = getSettingValue($guild, SettingTypeEnum::MIN_RANK_UP_TIME->value);
        $min_duty = getSettingValue($guild, SettingTypeEnum::MIN_DUTY->value);

        $users = $guild->users()
            ->select('users.discord_id')
            ->withSum('duties as total_duty_time', 'value')
            ->get();

        foreach ($users as $user) {
            $user_rank_up = ($user->total_duty_time >= ($min_rank_up_duty * 60) && Carbon::parse($user->pivot->last_role_time)->addDays($min_rank_up_time)->isPast()) && (is_null($user->pivot->last_warn_time) || Carbon::parse($user->pivot->last_warn_time)->addDays($next_checking_time)->isPast());
            $user_warn = ($user->total_duty_time < ($min_duty * 60)) &&
                (is_null($user->pivot->freedom_expiring) || Carbon::parse($user->pivot->freedom_expiring)->lt(Carbon::now()->subDays($next_checking_time)))
            && ($user->pivot->created_at->addDays($next_checking_time)->isPast());

            if ($is_executing) {
                $this->processUserActions($guild, $user, $user_rank_up, $user_warn);
                $guild->duties()->delete();
            }

            $user->rank_up = $user_rank_up;
            $user->warn = $user_warn;
        }

        return $users;
    }

    protected function processUserActions(Guild $guild, $user, bool $should_rank_up, bool $should_warn): void
    {
        try {
            $current_roles = getMemberData($guild->guild_id, $user->discord_id)['roles'] ?? [];

            if ($should_rank_up) {
                $this->handleRankUp($guild, $user, $current_roles);
            }

            if ($should_warn) {
                $this->handleWarn($guild, $user, $current_roles);
            }

        } catch (\Exception $e) {
            logger()->error("Duty check error for user {$user->discord_id}: ".$e->getMessage());
        }
    }

    protected function handleRankUp(Guild $guild, $user, array $current_roles): void
    {
        $user->pivot->last_role_time = now();
        $user->pivot->save();

        $ic_roles = getRoleValue($guild, 'ic_roles', []);
        $next_role = $this->getNextRole($current_roles, $ic_roles);

        if ($next_role) {
            $new_roles = array_diff($current_roles, $ic_roles);
            $new_roles[] = $next_role;
            changeMemberData($guild->guild_id, $user->discord_id, $new_roles);
        }
    }

    protected function handleWarn(Guild $guild, $user, array $current_roles): void
    {
        $user->pivot->last_warn_time = now();
        $user->pivot->save();

        $warn_roles = getRoleValue($guild, 'warn_roles', []);
        $next_warn_role = $this->getNextRole($current_roles, $warn_roles);

        if ($next_warn_role) {
            $new_roles = array_diff($current_roles, $warn_roles);
            $new_roles[] = $next_warn_role;
            changeMemberData($guild->guild_id, $user->discord_id, $new_roles);
        }
    }

    protected function getNextRole(array $current_roles, array $roles): ?string
    {
        foreach ($roles as $i => $role) {
            if (in_array($role, $current_roles)) {
                return $roles[$i + 1] ?? null;
            }
        }

        return $roles[0] ?? null;
    }
}
