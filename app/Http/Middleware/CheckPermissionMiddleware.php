<?php

namespace App\Http\Middleware;

use App\Enums\PermissionEnum;
use App\Models\GuildSelector;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        try {
            $required_permission = PermissionEnum::from($permission);
        } catch (\ValueError $e) {
            abort(400, 'Érvénytelen jogosultság-típus.');
        }

        $guild = Guildselector::getGuild();

        if (auth()->user()->cannot('hasPermission', [$guild, $required_permission])) {
            abort(403, 'Nincs jogosultságod.');
        }

        return $next($request);
    }
}
