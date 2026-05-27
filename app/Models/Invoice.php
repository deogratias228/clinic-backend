<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Facture liée à une visite.
 * status : draft | issued | paid | partial | cancelled.
 * paid_amount est recalculé à chaque nouveau paiement.
 * Le numéro (number) est généré par InvoiceService au format INV-YYYY-XXXX.
 */
class Invoice extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'visit_id',
        'patient_id',
        'number',
        'status',
        'total_amount',
        'paid_amount',
        'discount',
        'currency',
        'notes',
        'issued_at',
        'due_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_at');
    }

    // ─── Accesseurs ───────────────────────────────────────────────────────────

    /** Montant restant à payer. */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount - (float) $this->discount);
    }

    /** Indique si la facture est entièrement soldée. */
    public function isFullyPaid(): bool
    {
        return $this->remaining_amount <= 0;
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /** Factures non soldées (impayées ou partielles). */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['issued', 'partial']);
    }
}