<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlackListRequest;
use App\Models\Guild;
use App\Models\User;

class BlackListController extends Controller
{
    public function getBlackList(Guild $guild, User $user, BlackListRequest $request)
    {
        $validated = $request->validated();

        $blacklist = $guild->blacklists()->where('user_discord_id', $user->discord_id)->first();

        if ($blacklist) {
            return response()->json([
                'message' => 'A felhasználó feketelistán van.',
                'reason' => $blacklist->reason,
            ], 200);
        }

        return response()->json(['message' => 'A felhasználó nincsen feketelistán.'], 404);
    }

    public function getAllBlackList(Guild $guild)
    {

        $blacklists = $guild->blacklists()->get();

        return response()->json([
            'message' => 'Sikeresen lekérdezve a guild feketelistája.',
            'blacklist' => $blacklists,
        ], 200);
    }

    public function addBlackList(Guild $guild, User $user, BlackListRequest $request)
    {
        $validated = $request->validated();

        $exits = $user->blacklists()->where('guild_guild_id', $guild->guild_id)->exists();

        if ($exits) {
            return response()->json(['message' => 'A felhasználó már a feketelistán van.'], 409);
        }

        $user->blacklists()->create([
            'guild_guild_id' => $guild->guild_id,
            'reason' => $validated['reason'] ?? 'Nincsen indok',
        ]);

        return response()->json(['message' => 'A felhasználó blacklistre tétele sikeresen megtörtént.'], 200);
    }

    public function removeBlackList(Guild $guild, User $user)
    {
        $guild->blacklists()->where('user_discord_id', $user->discord_id)->delete();

        return response()->json(['message' => 'Sikeresen törölve a felhasználó a feketelistáról.'], 200);
    }
}
