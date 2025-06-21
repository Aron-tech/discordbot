<?php

namespace App\Http\Middleware;

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
        if (!GuildSelector::hasGuild()) {
            return to_route('guild.selector');
        }

        return $next($request);
    }
}
