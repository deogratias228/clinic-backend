<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Service de gestion des permissions et rôles.
 * Centralise la logique RBAC pour ne pas la dupliquer dans les controllers.
 */
class PermissionService
{
    /**
     * Retourne toutes les permissions disponibles groupées par module.
     * Utilisé pour l'interface de gestion des rôles.
     */
    public function allGrouped(): Collection
    {
        return Permission::all()
            ->groupBy('module')
            ->map(fn($perms) => $perms->sortBy('name')->values());
    }

    /**
     * Synchronise les permissions d'un rôle.
     * Remplace toutes les permissions existantes par la nouvelle liste.
     */
    public function syncRolePermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Ajoute ou retire une permission individuelle à un utilisateur.
     * granted=true  : accorde même si le rôle ne la possède pas.
     * granted=false : retire même si le rôle la possède.
     */
    public function overrideUserPermission(User $user, string $permissionName, bool $granted): void
    {
        $permission = Permission::where('name', $permissionName)->firstOrFail();

        $user->permissionOverrides()->syncWithoutDetaching([
            $permission->id => ['granted' => $granted],
        ]);
    }

    /**
     * Réinitialise toutes les surcharges d'un utilisateur (retour aux permissions du rôle).
     */
    public function resetUserOverrides(User $user): void
    {
        $user->permissionOverrides()->detach();
    }

    /**
     * Retourne la liste complète des permissions effectives d'un utilisateur.
     * Cache-able en production (Redis, 5 min) car appelé à chaque requête authentifiée.
     */
    public function effectivePermissions(User $user): Collection
    {
        return $user->effectivePermissions();
    }

    /**
     * Vérifie si un utilisateur peut effectuer une action.
     * Point d'entrée unique pour tous les controllers.
     */
    public function userCan(User $user, string $permission): bool
    {
        return $user->can($permission);
    }

    /**
     * Interrompt la requête avec 403 si l'utilisateur n'a pas la permission.
     */
    public function authorize(User $user, string $permission): void
    {
        abort_if(!$this->userCan($user, $permission), 403, "Permission refusée : {$permission}");
    }
}
