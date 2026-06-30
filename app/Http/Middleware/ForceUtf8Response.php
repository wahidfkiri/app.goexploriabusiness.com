<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceUtf8Response
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contentType = (string) $response->headers->get('Content-Type', '');
        $lower = strtolower($contentType);

        if ($contentType === '') {
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
            return $response;
        }

        if (
            str_contains($lower, 'text/html')
            && ! str_contains($lower, 'charset=')
        ) {
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
        }

        return $response;
    }
}

