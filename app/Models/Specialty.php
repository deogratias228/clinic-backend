<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Spécialité médicale.
 */
class Specialty extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'name',
        'color',
        'sort_order'
    ];

    protected $casts = ['sort_order' => 'integer'];

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }
}