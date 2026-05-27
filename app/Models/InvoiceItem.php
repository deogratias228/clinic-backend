<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ligne d'une facture.
 * act_catalog_id nullable : la ligne peut être saisie manuellement sans référence au catalogue.
 * label est toujours renseigné (copié depuis le catalogue ou saisi à la main).
 * total = quantity * unit_price, calculé avant insertion.
 */
class InvoiceItem extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'invoice_id',
        'act_catalog_id',
        'label',
        'quantity',
        'unit_price',
        'total',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function actCatalog(): BelongsTo
    {
        return $this->belongsTo(ActCatalog::class);
    }
}