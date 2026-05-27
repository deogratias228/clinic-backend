<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sérialise un rôle.
 * users_count et permissions_count disponibles si withCount() est utilisé dans la requête.
 */
class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'label'            => $this->label,
            'is_default'       => $this->is_default,
            'is_system'        => $this->is_system,
            'users_count'      => $this->whenCounted('users'),
            'permissions_count'=> $this->whenCounted('permissions'),
            'permissions'      => $this->whenLoaded('permissions', fn () => PermissionResource::collection($this->permissions)),
        ];
    }
}
