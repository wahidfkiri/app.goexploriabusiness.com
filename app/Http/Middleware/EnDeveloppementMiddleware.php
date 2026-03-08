<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnDeveloppementMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si l'utilisateur est connecté
        if (Auth::check()) {
            // Message pour l'utilisateur connecté
            Auth::logout(); // Déconnecter l'utilisateur
            return redirect()->back()->with('message', 'Cette fonctionnalité est en cours de développement');
        }

        // Si l'utilisateur n'est pas connecté, continuer normalement
        return $next($request);
    }
}