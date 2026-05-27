<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Rôle utilisateur.
 * Les rôles système (is_system=true) ne peuvent pas être supprimés.
 * Chaque rôle porte un ensemble de permissions modifiable par l'admin.
 */
class Role extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'name',
        'label',
        'is_default',
        'is_system'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /** Vérifie si le rôle possède une permission par son slug. */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions->contains('name', $permissionName);
    }
}