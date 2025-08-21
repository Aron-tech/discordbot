<?php

namespace App\Http\Controllers\Api;

use App\Actions\CheckingDutyAction;
use App\Enums\Guild\ChannelTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\DutyRequest;
use App\Models\Duty;
use App\Models\Guild;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DutyController extends Controller
{
    public function getUserDuty(Guild $guild, User $user): JsonResponse
    {
        $total_duty_time = $user->totalDutyTime($guild);

        $period_duty_time = $user->periodDutyTime($guild);

        $user_pivot = $guild->users()
            ->where('user_discord_id', $user->discord_id)
            ->first()?->pivot ?? null;

        if ($user_pivot->last_role_time) {
            $last_role_days = (int) Carbon::parse($user_pivot->last_role_time)->diffInDays(now());
        } else {
            $last_role_days = (int) Carbon::parse($user->created_at)->diffInDays(now());
        }

        return response()->json([
            'message' => 'A felhasználó szolgálati idejének lekérdezése sikeresen megtörtént',
            'total_duty_time' => $total_duty_time,
            'period_duty_time' => $period_duty_time,
            'last_role_days' => $last_role_days,
            'last_warn_time' => $user_pivot->last_warn_time ?? null,
        ], 200);
    }

    public function getAllDuty(Guild $guild): JsonResponse
    {
        $total_duties = $guild->duties()->withTrashed()
            ->groupBy('user_discord_id', 'guild_guild_id')
            ->selectRaw('user_discord_id, guild_guild_id, SUM(value) as total_value')
            ->get();

        $period_duties = $guild->duties()
            ->groupBy('user_discord_id', 'guild_guild_id')
            ->selectRaw('user_discord_id, guild_guild_id, SUM(value) as total_value')
            ->get();

        return response()->json([
            'message' => 'A felhasználók szolgálati idejének lekérdezése sikeresen megtörtént',
            'total_duties' => $total_duties,
            'period_duties' => $period_duties,
        ], 200);
    }

    public function onDuty(Guild $guild, User $user): JsonResponse
    {
        $exits = $guild->duties()->where('user_discord_id', $user->discord_id)
            ->whereNull('end_time')
            ->whereNull('value')
            ->exists();

        if ($exits) {
            return response()->json(['message' => 'A felhasználó már szolgálatban van.'], 409);
        }

        $guild->duties()->create([
            'user_discord_id' => $user->discord_id,
            'start_time' => now(),
        ]);

        return response()->json([
            'message' => 'Sikeresen szolgálatba lépett.',
        ], 200);
    }

    public function offDuty(Guild $guild, User $user): JsonResponse
    {
        $duty = $guild->duties()
            ->where('user_discord_id', $user->discord_id)
            ->whereNull('end_time')
            ->whereNull('value')
            ->first();

        if (! $duty) {
            return response()->json(['message' => 'A felhasználó szolgálatba lépésének ideje nem található.'], 404);
        }

        $end_time = now();

        $duty_time = $duty->start_time->diffInMinutes($end_time);

        $duty->update([
            'end_time' => $end_time,
            'value' => $duty_time,
        ]);

        $user_pivot = $guild->users()
            ->where('user_discord_id', $user->discord_id)
            ->first()?->pivot ?? null;

        if ($user_pivot->last_role_time) {
            $last_role_days = (int) Carbon::parse($user_pivot->last_role_time)->diffInDays(now());
        } else {
            $last_role_days = (int) Carbon::parse($user_pivot->created_at)->diffInDays(now());
        }

        return response()->json([
            'message' => 'A felhasználó sikeresen kilépett a szolgálatból.',
            'duty_time' => $duty_time,
            'period_duty_time' => $user->periodDutyTime($guild),
            'last_role_days' => $last_role_days,
            'last_warn_time' => $user_pivot->last_warn_time ?? null,
        ], 200);
    }

    public function cancelDuty(Guild $guild, User $user): JsonResponse
    {
        $duty = $guild->duties()
            ->where('user_discord_id', $user->discord_id)
            ->whereNull('end_time')
            ->whereNull('value')
            ->first();

        if (! $duty) {
            return response()->json(['message' => 'A felhasználó szolgálatba lépésének ideje nem található.'], 404);
        }

        $duty->forceDelete();

        return response()->json([
            'message' => 'A felhasználó sikeresen kiléptetve a szolgálatból',
            'start_time' => $duty->start_time,
        ], 200);
    }

    public function addDuty(Guild $guild, User $user, DutyRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $guild->duties()->create([
            'user_discord_id' => $user->discord_id,
            'start_time' => now(),
            'end_time' => now(),
            'value' => $validated['duty_time'],
        ]);

        return response()->json(['message' => 'Sikeresen hozzáadva a szolgálati idejéhez.'], 200);
    }

    public function deleteUserDuty(Guild $guild, User $user): JsonResponse
    {
        $guild->duties()
            ->where('user_discord_id', $user->discord_id)
            ->delete();

        return response()->json(['message' => 'Sikeresen törölve a felhasználó szolgálati ideje.'], 200);
    }

    public function deleteAllDuty(Guild $guild): JsonResponse
    {
        $guild->duties()->delete();

        return response()->json(['message' => 'Sikeresen törölted az összes szolgálati időt.'], 200);
    }

    public function clearDutyTrash(Guild $guild): JsonResponse
    {
        Duty::onlyTrashed()
            ->where('guild_guild_id', $guild->guild_id)
            ->forceDelete();

        return response()->json(['message' => 'Sikeresen törölve a szerver összes szolgálati ideje. (Régiek)'], 200);
    }

    public function getActiveDuty(Guild $guild): JsonResponse
    {
        $active_duties = $guild->duties()
            ->whereNull('value')
            ->whereNull('end_time')
            ->select('user_discord_id', 'start_time')
            ->get()
            ->map(function ($duty) use ($guild) {
                $pivot = $guild->users()
                    ->where('user_discord_id', $duty->user_discord_id)
                    ->first()?->pivot;

                return [
                    'discord_id' => $duty->user_discord_id,
                    'start_time' => $duty->start_time,
                    'ic_name' => $pivot?->ic_name,
                ];
            });

        return response()->json([
            'message' => 'Sikeresen lekérdezve a jelenleg szolgálatban lévő felhasználók listája',
            'active_duties' => $active_duties,
            'duty_channel' => getChannelValue($guild, ChannelTypeEnum::DUTY->value),
            'active_num_channel' => getChannelValue($guild, ChannelTypeEnum::ACTIVE_NUM->value),
        ], 200);
    }

    public function checkingDuty(Guild $guild, DutyRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['is_executing'])) {
            $users = CheckingDutyAction::run($guild, true);
        } else {
            $users = CheckingDutyAction::run($guild);
        }

        return response()->json([
            'message' => 'Sikeresen kilistázva az ellenőrzés eredménye.',
            'users' => $users,
        ], 200);
    }
}
