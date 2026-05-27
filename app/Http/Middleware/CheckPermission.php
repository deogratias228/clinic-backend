<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie qu'un utilisateur authentifié possède la permission requise.
 * Usage dans les routes : middleware('permission:appointments.create')
 * Le super_admin bypass toutes les vérifications (géré dans User::can()).
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        abort_if(!$user, 401, 'Non authentifié.');
        abort_if(!$user->can($permission), 403, "Permission refusée : {$permission}");

        return $next($request);
    }
}