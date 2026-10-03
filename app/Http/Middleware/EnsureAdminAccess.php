<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdminAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
