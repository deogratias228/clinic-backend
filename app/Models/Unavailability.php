<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Indisponibilité ponctuelle d'un médecin.
 * start_time et end_time null = journée entière bloquée.
 */
class Unavailability extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'reason'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** Indique si l'indisponibilité couvre toute la journée. */
    public function isFullDay(): bool
    {
        return is_null($this->start_time) && is_null($this->end_time);
    }
}