<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Acte médical du catalogue du cabinet.
 * Chaque cabinet configure ses actes et tarifs via l'interface admin.
 * Les lignes de facture référencent un acte du catalogue ou sont saisies manuellement.
 */
class ActCatalog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'act_catalog';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'name',
        'category',
        'default_price',
        'currency',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}