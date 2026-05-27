<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sérialise un utilisateur.
 * permissions_count : nombre de permissions effectives (affiché dans la liste admin).
 * effective_permissions : chargé uniquement sur les routes show/me.
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'email'     => $this->email,
            'phone'     => $this->phone,
            'is_active' => $this->is_active,
            'role'      => $this->whenLoaded('role', fn () => RoleResource::make($this->role)),
            'doctor'    => $this->whenLoaded('doctor', fn () => DoctorResource::make($this->doctor)),
            'created_at'=> $this->created_at->toDateString(),
        ];
    }
}
