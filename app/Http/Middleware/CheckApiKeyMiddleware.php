<?php

namespace App\Http\Middleware;

use Closure;

class CheckApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // app/Http/Middleware/CheckApiKey.php
    public function handle($request, Closure $next)
    {
        if ($request->header('Authorization') !== 'Bearer ' . config('services.api.key')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
