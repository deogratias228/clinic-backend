<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Consultation médicale rédigée par le médecin.
 * Module optionnel par médecin (uses_consultation_module).
 * Une visite peut exister sans consultation enregistrée dans l'outil
 * si le médecin préfère un support papier ou n'utilise pas le module.
 */
class Consultation extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'visit_id',
        'doctor_id',
        'symptoms',
        'diagnosis',
        'treatment',
        'notes',
        'follow_up',
        'consulted_at',
    ];

    protected $casts = [
        'consulted_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
}