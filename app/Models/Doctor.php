<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Médecin du cabinet.
 * Peut être lié à un compte utilisateur (user_id) pour accéder au back-office.
 * uses_consultation_module : opt-in par médecin, indépendamment du module global.
 * accepts_online_booking   : peut être désactivé sans désactiver le médecin.
 */
class Doctor extends Model
{
    use HasFactory, HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'specialty_id',
        'name',
        'phone',
        'photo_path',
        'bio',
        'slot_duration_minutes',
        'accepts_online_booking',
        'uses_consultation_module',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'slot_duration_minutes'    => 'integer',
        'accepts_online_booking'   => 'boolean',
        'uses_consultation_module' => 'boolean',
        'is_active'                => 'boolean',
        'sort_order'               => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function unavailabilities(): HasMany
    {
        return $this->hasMany(Unavailability::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }
}