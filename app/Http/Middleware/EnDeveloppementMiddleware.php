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
            // Déconnecter l'utilisateur
            Auth::logout();
            
            // Invalider la session
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // Rediriger vers la page de login avec un message
            return redirect()->route('login')
                ->with('message', 'Cette fonctionnalité est en cours de développement. Veuillez vous reconnecter plus tard.');
        }

        // Si l'utilisateur n'est pas connecté, continuer normalement
        return $next($request);
    }
}