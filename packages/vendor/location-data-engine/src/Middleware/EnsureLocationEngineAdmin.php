<?php

namespace Vendor\LocationDataEngine\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLocationEngineAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $isAllowed = $user
            && (
                (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('super-admin')))
                || (bool) data_get($user, 'is_admin', false)
            );

        if (! $isAllowed) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => 'Unauthorized for location data engine.',
                ], 403);
            }

            abort(403, 'Unauthorized for location data engine.');
        }

        return $next($request);
    }
}
