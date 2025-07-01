<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CreateUserPivotRequest;
use App\Http\Requests\UpdateUserPivotRequest;
use App\Http\Requests\UserRequest;
use App\Models\Guild;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController
{
    public function addUser(Guild $guild, UserRequest $request, CreateUserPivotRequest $pivot_request): JsonResponse
    {
        $validated = $request->validated();

        $pivot_validated = $pivot_request->validated();

        $user = User::firstOrCreate(
            ['discord_id' => $validated['discord_id']],
            [
                'discord_id' => $validated['discord_id'],
                'name' => $validated['name'],
                'avatar' => $validated['avatar'] ?? null,
                'email' => $validated['email'] ?? null,
            ]
        );

        $blacklist = $user->blacklists()->where('black_lists.guild_guild_id', $guild->guild_id)->first();

        if ($blacklist) {
            return response()->json([
                'message' => 'A felhasználó jelenleg feketelistán van.',
                'reason' => $blacklist->reason ?? 'Nincsen indok',
            ], 400);
        }

        $existing = $guild->users()->where('user_discord_id', $user->discord_id)->exists();

        if ($existing) {
            return response()->json(['message' => 'A felhasználó már a guild tagja.'], 409);
        }

        $guild->users()->syncWithoutDetaching([
            $user->discord_id => [
                'ic_name' => $pivot_validated['ic_name'],
                'ic_number' => $pivot_validated['ic_number'],
                'ic_tel' => $pivot_validated['ic_tel'] ?? null,
            ],
        ]);

        return response()->json(['message' => 'A felhasználó sikeresen fel lett véve.'], 200);
    }

    public function getUser(Guild $guild, User $user): JsonResponse
    {

        $pivot = $guild->users()->where('user_discord_id', $user->discord_id)->first()->pivot ?? null;

        $total_duty_time = $user->totalDutyTime($guild);
        $period_duty_time = $user->periodDutyTime($guild);

        return response()->json([
            'message' => 'Sikeresen lekérdezted a felhasználó adatait',
            'ic_data' => $pivot,
            'total_duty_time' => $total_duty_time,
            'period_duty_time' => $period_duty_time,
        ], 200);
    }

    public function removeUser(Guild $guild, User $user): JsonResponse
    {
        $guild->users()->detach($user->discord_id);

        $guild->duties()->where('user_discord_id', $user->discord_id)->delete();

        return response()->json(['message' => 'Sikeresen törölted a felhasználót'], 200);
    }

    public function updateUserPivot(Guild $guild, User $user, UpdateUserPivotRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $pivot = $guild->users()->where('user_discord_id', $user->discord_id)->first()->pivot ?? null;

        $guild->users()->updateExistingPivot($user->discord_id, [
            'ic_name' => $validated['ic_name'] ?? $pivot->ic_name,
            'ic_number' => $validated['ic_number'] ?? $pivot->ic_number,
            'ic_tel' => $validated['ic_tel'] ?? $pivot->ic_tel,
            'last_role_time' => $validated['last_role_time'] ?? ($pivot->last_role_time ?? $pivot->created_at),
            'last_warn_time' => $validated['last_warn_time'] ?? $pivot->last_warn_time,
            'freedom_expiring' => $validated['freedom_expiring'] ?? $pivot->freedom_expiring,
        ]);

        return response()->json(['messsage' => 'A felhasználó információjának módosítása sikeresen megtörtént.'], 200);
    }
}
