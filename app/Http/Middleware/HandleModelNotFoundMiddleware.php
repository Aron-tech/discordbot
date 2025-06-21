<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class HandleModelNotFoundMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'A keresett erőforrás nem található.',
            ], 404);
        }
    }
}
