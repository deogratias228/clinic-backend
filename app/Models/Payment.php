<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Versement sur une facture.
 * Une facture peut avoir plusieurs versements de méthodes différentes.
 * method : cash | mobile_money | card | insurance | transfer.
 * reference : numéro de transaction (Mobile Money, virement, etc.).
 * user_id : caissier qui a enregistré le paiement — traçabilité obligatoire.
 */
class Payment extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'invoice_id',
        'user_id',
        'amount',
        'method',
        'reference',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /** Caissier qui a enregistré le paiement. */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}