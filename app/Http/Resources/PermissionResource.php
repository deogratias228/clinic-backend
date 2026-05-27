<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Sérialise une permission atomique. */
class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'module' => $this->module,
            'label'  => $this->label,
        ];
    }
}
