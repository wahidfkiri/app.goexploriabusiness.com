<?php

namespace Vendor\MapsDataEngine\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMapsDataEngineAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Authentication required.');
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super-admin'])) {
            return $next($request);
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('super-admin'))) {
            return $next($request);
        }

        if ((bool) data_get($user, 'is_admin', false)) {
            return $next($request);
        }

        abort(403, 'Admin access required.');
    }
}
