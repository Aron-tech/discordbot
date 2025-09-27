<?php

namespace App\Actions;

use App\Enums\Guild\ChannelTypeEnum;
use App\Enums\Guild\SettingTypeEnum;
use App\Livewire\Traits\DcMessageTrait;
use App\Models\Guild;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckingUserDutyAction
{
    use AsAction;
    use DcMessageTrait;
    public function handle(Guild $guild, $discord_id, array $system_data): void
    {
        try {
            DB::beginTransaction();
            $user = $guild->users()->where('discord_id', $discord_id)->first();

            $min_duty = $system_data['min_duty'];
            $min_rank_up_duty = $system_data['min_rank_up_duty'];
            $next_checking_time = $system_data['next_checking_time'];
            $min_rank_up_time = $system_data['min_rank_up_time'];

            $should_rank_up = ($user->total_duty_time >= ($min_rank_up_duty * 60) && Carbon::parse($user->pivot->last_role_time)->addDays($min_rank_up_time)->isPast()) && (is_null($user->pivot->last_warn_time) || Carbon::parse($user->pivot->last_warn_time)->addDays($next_checking_time)->isPast());
            $should_warn = ($user->total_duty_time < ($min_duty * 60)) &&
                (is_null($user->pivot->freedom_expiring) || Carbon::parse($user->pivot->freedom_expiring)->lt(Carbon::now()->subDays($next_checking_time)))
                && ($user->pivot->created_at->addDays($next_checking_time)->isPast());

            $current_roles = getMemberData($guild->guild_id, $user->discord_id)['roles'] ?? [];

            if ($should_rank_up) {
                $this->handleRankUp($guild, $user, $current_roles);
            }

            if ($should_warn) {
                $this->handleWarn($guild, $user, $current_roles);
            }

            $guild->duties()->where('user_discord_id', $user->discord_id)->delete();

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            logger()->error("Duty check error for user {$user->discord_id}: " . $e->getMessage());
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
            changeMemberRole($guild->guild_id, $user->discord_id, $new_roles);
        }
    }

    /**
     * @throws Exception
     */
    protected function handleWarn(Guild $guild, $user, array $current_roles): void
    {
        $user->pivot->last_warn_time = now();
        $user->pivot->save();

        $warn_roles = getRoleValue($guild, 'warn_roles', []);
        $next_warn_role = $this->getNextRole($current_roles, $warn_roles);
        $next_warn_index = array_search($next_warn_role, $warn_roles, true);

        $warn_channel = getChannelValue($guild, ChannelTypeEnum::WARN->value);
        $embed = [
            'title' => '⚠️ Figyelmeztetés',
            'color' => hexdec('FF0000'),
            'fields' => [
                [
                    'name' => '👤 Felhasználó',
                    'value' => '<@' . $user->discord_id . '>',
                    'inline' => true,
                ],
                [
                    'name' => '📊 Figyelmeztetési szint',
                    'value' => (string)(($next_warn_index + 1) . '.'),
                    'inline' => true,
                ],
                [
                    'name' => '🛡️ Moderátor',
                    'value' => 'Rendszer',
                    'inline' => true,
                ],
                [
                    'name' => '📄 Indok',
                    'value' => '```Inaktivitás```',
                    'inline' => false,
                ],
            ],
            'footer' => [
                'text' => 'Duty Management System • Elküldve: ' . now()->locale('hu')->translatedFormat('Y.m.d H:i:s'),
            ],
        ];

        $this->sendEmbed($warn_channel, $embed);

        if ($next_warn_role) {
            $new_roles = array_diff($current_roles, $warn_roles);
            $new_roles[] = $next_warn_role;
            changeMemberRole($guild->guild_id, $user->discord_id, $new_roles);
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
