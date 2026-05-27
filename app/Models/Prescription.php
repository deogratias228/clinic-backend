<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ordonnance générée depuis une consultation.
 * content : texte libre formaté (médicaments, posologies, durée).
 * is_printed : suivi d'impression pour l'interface médecin.
 */
class Prescription extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'consultation_id',
        'content',
        'valid_until',
        'is_printed',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'is_printed' => 'boolean',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}