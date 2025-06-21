<?php

namespace App\Http\Middleware;

use App\Enums\PermissionEnum;
use App\Models\GuildSelector;
use Closure;
use Illuminate\Http\Request;

class ValidateGuildSelectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (! GuildSelector::hasGuild()) {
            return to_route('guild.selector');
        }

        $guild = GuildSelector::getGuild();

        if (! $guild->installed && auth()->user()->can('hasPermission', [$guild, PermissionEnum::VIEW_SETTINGS])) {
            return to_route('admin.install');
        } elseif (! $guild->installed) {
            return to_route('guild.selector')->with('error', 'Nincs telepítve a discord bot, keresd fel a szerver tulajdonosát.');
        }

        return $next($request);
    }
}
