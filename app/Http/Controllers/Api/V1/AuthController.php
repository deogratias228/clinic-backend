<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Authentification back-office via Sanctum SPA token.
 */
class AuthController extends Controller
{
    /**
     * Authentifie un utilisateur et retourne un token Sanctum.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }

        $user = Auth::user();

        abort_if(! $user->is_active, 403, 'Compte désactivé. Contactez l\'administrateur.');

        $token = $user->createToken('spa-token', ['*'], now()->addDays(30))->plainTextToken;
        Log::info('Utilisateur connecté', ['user_id' => $user->id, 'email' => $user->email, 'token_id' => $token]);
        return response()->json([
            'token' => $token,
            'user'  => UserResource::make($user->load('role')),
        ]);
    }

    /**
     * Révoque le token courant et déconnecte l'utilisateur.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    /**
     * Retourne l'utilisateur authentifié avec son rôle et ses permissions effectives.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('role.permissions', 'permissionOverrides');

        return response()->json([
            'user'        => UserResource::make($user),
            'permissions' => $user->effectivePermissions()->pluck('name'),
        ]);
    }
}
