<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureModuleAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $module): mixed
    {
        $user = Auth::user();

        if (! $user || ! $user->hasModuleAccess($module)) {
            abort(403, 'You do not have access to this module.');
        }

        return $next($request);
    }
}
