<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

/**
 * Utilisateur du back-office.
 * Les permissions effectives combinent celles du rôle + les surcharges individuelles.
 * Un utilisateur peut être lié à un médecin (pour l'accès à son agenda et ses consultations).
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** Surcharges individuelles de permissions (granted ou retirées). */
    public function permissionOverrides(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot('granted');
    }

    /** Fiche médecin liée à ce compte, si l'utilisateur est un praticien. */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    // ─── RBAC ─────────────────────────────────────────────────────────────────

    /**
     * Retourne la liste effective des permissions de l'utilisateur.
     * Logique : permissions du rôle + surcharges individuelles.
     * Une surcharge granted=false retire une permission même si le rôle la possède.
     */
    public function effectivePermissions(): Collection
    {
        $rolePermissions = $this->role->permissions->keyBy('name');

        foreach ($this->permissionOverrides as $permission) {
            if ($permission->pivot->granted) {
                $rolePermissions->put($permission->name, $permission);
            } else {
                $rolePermissions->forget($permission->name);
            }
        }

        return $rolePermissions->values();
    }

    /**
     * Vérifie si l'utilisateur possède une permission donnée.
     * Le rôle super_admin bypass toutes les vérifications.
     */
    public function can($ability, mixed $arguments = []): bool
    {
        if ($this->role->name === 'super_admin') {
            return true;
        }

        return $this->effectivePermissions()->contains('name', $ability);
    }

    /** Vérifie plusieurs permissions (toutes requises). */
    public function canAll(array $abilities): bool
    {
        foreach ($abilities as $ability) {
            if (!$this->can($ability))
                return false;
        }
        return true;
    }

    /** Vérifie au moins une permission parmi la liste. */
    public function canAny($abilities, $arguments = []): bool
    {
        foreach ($abilities as $ability) {
            if ($this->can($ability))
                return true;
        }
        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role->name === 'super_admin';
    }
}