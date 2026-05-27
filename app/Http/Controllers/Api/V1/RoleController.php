<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;

/**
 * Gestion des rôles et de leurs permissions.
 * Les rôles système (is_system=true) ne peuvent pas être supprimés.
 */
class RoleController extends Controller
{
    public function __construct(private readonly PermissionService $permissionService) {}

    /**
     * Liste tous les rôles avec leur nombre de permissions.
     */
    public function index(): JsonResponse
    {
        $roles = Role::withCount('users', 'permissions')->orderBy('name')->get();

        return response()->json(['data' => RoleResource::collection($roles)]);
    }

    /**
     * Détail d'un rôle avec ses permissions groupées par module.
     */
    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');

        return response()->json([
            'data'        => RoleResource::make($role),
            'permissions' => $role->permissions->groupBy('module'),
        ]);
    }

    /**
     * Retourne toutes les permissions disponibles groupées par module.
     * Utilisé pour construire l'interface d'édition d'un rôle.
     */
    public function allPermissions(): JsonResponse
    {
        return response()->json([
            'data' => $this->permissionService->allGrouped(),
        ]);
    }

    /**
     * Crée un nouveau rôle personnalisé.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::create([
            'name'       => $request->validated('name'),
            'label'      => $request->validated('label'),
            'is_default' => $request->validated('is_default', false),
            'is_system'  => false,
        ]);

        if ($request->has('permission_ids')) {
            $this->permissionService->syncRolePermissions($role, $request->validated('permission_ids'));
        }

        return response()->json(['data' => RoleResource::make($role->load('permissions'))], 201);
    }

    /**
     * Met à jour le libellé et les permissions d'un rôle.
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role->update($request->only(['label', 'is_default']));

        if ($request->has('permission_ids')) {
            $this->permissionService->syncRolePermissions($role, $request->validated('permission_ids'));
        }

        return response()->json(['data' => RoleResource::make($role->load('permissions'))]);
    }

    /**
     * Supprime un rôle personnalisé.
     * Bloque si le rôle est système ou s'il a des utilisateurs actifs.
     */
    public function destroy(Role $role): JsonResponse
    {
        abort_if($role->is_system, 422, 'Un rôle système ne peut pas être supprimé.');
        abort_if($role->users()->exists(), 422, 'Ce rôle est attribué à des utilisateurs actifs.');

        $role->delete();

        return response()->json(null, 204);
    }
}
