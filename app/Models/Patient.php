<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Dossier patient du cabinet.
 * Entité distincte des utilisateurs — un patient n'a pas de compte.
 * Le dossier s'enrichit à chaque visite : consultations, factures, prescriptions.
 */
class Patient extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'birth_date',
        'gender',
        'address',
        'blood_type',
        'allergies',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // ─── Accesseurs ───────────────────────────────────────────────────────────

    /** Nom complet du patient. */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /** Âge calculé depuis la date de naissance. */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }
}