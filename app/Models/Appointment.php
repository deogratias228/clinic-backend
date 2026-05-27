<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Rendez-vous pris en ligne ou créé manuellement par le cabinet.
 * status  : pending | confirmed | cancelled | no_show | done.
 * source  : online | manual.
 * patient_id nullable : un RDV en ligne est créé avant la fiche patient.
 * patient_name/patient_phone : données saisies en ligne, copiées sur la fiche patient à l'accueil.
 */
class Appointment extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'source',
        'patient_name',
        'patient_phone',
        'reason',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** Visite générée depuis ce rendez-vous, si le patient s'est présenté. */
    public function visit(): HasOne
    {
        return $this->hasOne(Visit::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /** Filtre par date. */
    public function scopeForDate($query, string $date)
    {
        return $query->where('appointment_date', $date);
    }

    /** Filtre les statuts actifs (non annulés, non no_show). */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'no_show']);
    }
}