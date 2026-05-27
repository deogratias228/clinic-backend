<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Permission atomique.
 * Format du slug name : module.action — ex: appointments.create, billing.view.
 * Groupées par module pour l'affichage dans l'interface de gestion des rôles.
 */
class Permission extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'name',
        'module',
        'label'
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions')
            ->withPivot('granted');
    }
}