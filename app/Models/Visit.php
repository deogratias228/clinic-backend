<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Visite réelle d'un patient au cabinet.
 * Découplée du rendez-vous : une visite peut exister sans RDV (walk-in).
 *
 * Flux configurable via status :
 *   registered → awaiting_payment → paid → in_consultation → done
 *
 * Les cabinets simples sautent awaiting_payment/paid et passent directement
 * de registered à in_consultation selon leur configuration.
 *
 * arrived_at, seen_at, done_at permettent de mesurer les temps d'attente réels.
 */
class Visit extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'patient_id',
        'status',
        'reason',
        'arrived_at',
        'seen_at',
        'done_at',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'seen_at'    => 'datetime',
        'done_at'    => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    // ─── Accesseurs ───────────────────────────────────────────────────────────

    /** Temps d'attente en minutes (entre arrivée et début de consultation). */
    public function getWaitingMinutesAttribute(): ?int
    {
        if (! $this->arrived_at || ! $this->seen_at) return null;
        return (int) $this->arrived_at->diffInMinutes($this->seen_at);
    }

    /** Durée de consultation en minutes. */
    public function getConsultationMinutesAttribute(): ?int
    {
        if (! $this->seen_at || ! $this->done_at) return null;
        return (int) $this->seen_at->diffInMinutes($this->done_at);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /** Visites du jour. */
    public function scopeToday($query)
    {
        return $query->whereDate('arrived_at', today());
    }

    /** Visites en attente ou en cours. */
    public function scopeInProgress($query)
    {
        return $query->whereNotIn('status', ['done']);
    }
}