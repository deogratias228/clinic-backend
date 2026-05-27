<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Horaire récurrent d'un médecin par jour de semaine.
 * day_of_week : monday | tuesday | wednesday | thursday | friday | saturday | sunday.
 */
class Schedule extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active'
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}