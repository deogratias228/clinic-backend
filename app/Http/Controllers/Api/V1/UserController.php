<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UpdateUserPermissionsRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Gestion des utilisateurs du back-office.
 * Inclut la gestion des surcharges de permissions individuelles.
 */
class UserController extends Controller
{
    public function __construct(private readonly PermissionService $permissionService) {}

    /**
     * Liste de tous les utilisateurs avec leur rôle.
     */
    public function index(): JsonResponse
    {
        $users = User::with('role', 'doctor')->orderBy('name')->get();

        return response()->json(['data' => UserResource::collection($users)]);
    }

    /**
     * Détail d'un utilisateur avec ses permissions effectives.
     */
    public function show(User $user): JsonResponse
    {
        $user->load('role.permissions', 'permissionOverrides', 'doctor');

        return response()->json([
            'data'               => UserResource::make($user),
            'effective_permissions' => $user->effectivePermissions()->pluck('name'),
        ]);
    }

    /**
     * Crée un nouvel utilisateur.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            'role_id'   => $request->validated('role_id'),
            'name'      => $request->validated('name'),
            'email'     => $request->validated('email'),
            'password'  => Hash::make($request->validated('password')),
            'phone'     => $request->validated('phone'),
            'is_active' => $request->validated('is_active', true),
        ]);

        return response()->json(['data' => UserResource::make($user->load('role'))], 201);
    }

    /**
     * Met à jour un utilisateur.
     * Le mot de passe n'est mis à jour que s'il est fourni.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json(['data' => UserResource::make($user->load('role'))]);
    }

    /**
     * Active ou désactive un utilisateur.
     */
    public function toggleActive(User $user): JsonResponse
    {
        abort_if($user->isSuperAdmin(), 422, 'Le super administrateur ne peut pas être désactivé.');

        $user->update(['is_active' => ! $user->is_active]);

        return response()->json(['data' => UserResource::make($user->load('role'))]);
    }

    /**
     * Applique des surcharges de permissions individuelles à un utilisateur.
     * Remplace toutes les surcharges existantes.
     */
    public function updatePermissions(UpdateUserPermissionsRequest $request, User $user): JsonResponse
    {
        $this->permissionService->resetUserOverrides($user);

        foreach ($request->validated('overrides', []) as $override) {
            $this->permissionService->overrideUserPermission(
                $user,
                $override['permission'],
                $override['granted']
            );
        }

        return response()->json([
            'effective_permissions' => $user->fresh()->effectivePermissions()->pluck('name'),
        ]);
    }

    /**
     * Supprime un utilisateur.
     * Bloque la suppression du super_admin.
     */
    public function destroy(User $user): JsonResponse
    {
        abort_if($user->isSuperAdmin(), 422, 'Le super administrateur ne peut pas être supprimé.');

        $user->delete();

        return response()->json(null, 204);
    }
}
