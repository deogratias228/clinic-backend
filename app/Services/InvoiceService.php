<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Visit;

/**
 * Service de gestion de la facturation.
 * Centralise la génération de numéros, le calcul des totaux
 * et la mise à jour automatique du statut des factures.
 */
class InvoiceService
{
    /**
     * Crée une facture draft pour une visite.
     * Appelé à l'accueil ou à la caisse selon le flux du cabinet.
     */
    public function createForVisit(Visit $visit): Invoice
    {
        return Invoice::create([
            'visit_id'     => $visit->id,
            'patient_id'   => $visit->patient_id,
            'number'       => $this->generateNumber(),
            'status'       => 'draft',
            'total_amount' => 0,
            'paid_amount'  => 0,
            'discount'     => 0,
            'currency'     => setting('billing.currency', 'XOF'),
        ]);
    }

    /**
     * Ajoute une ligne à une facture et recalcule le total.
     */
    public function addItem(Invoice $invoice, array $data): InvoiceItem
    {
        $total = $data['quantity'] * $data['unit_price'];

        $item = $invoice->items()->create([
            'act_catalog_id' => $data['act_catalog_id'] ?? null,
            'label'          => $data['label'],
            'quantity'       => $data['quantity'],
            'unit_price'     => $data['unit_price'],
            'total'          => $total,
            'sort_order'     => $invoice->items()->count(),
        ]);

        $this->recalculateTotal($invoice);

        return $item;
    }

    /**
     * Supprime une ligne et recalcule le total.
     */
    public function removeItem(Invoice $invoice, InvoiceItem $item): void
    {
        $item->delete();
        $this->recalculateTotal($invoice);
    }

    /**
     * Enregistre un paiement sur une facture et met à jour le statut.
     */
    public function addPayment(Invoice $invoice, array $data): Payment
    {
        $payment = $invoice->payments()->create([
            'user_id'   => $data['user_id'],
            'amount'    => $data['amount'],
            'method'    => $data['method'],
            'reference' => $data['reference'] ?? null,
            'notes'     => $data['notes'] ?? null,
            'paid_at'   => $data['paid_at'] ?? now(),
        ]);

        $this->recalculatePaidAmount($invoice);

        return $payment;
    }

    /**
     * Recalcule le montant total depuis les lignes de la facture.
     */
    public function recalculateTotal(Invoice $invoice): void
    {
        $total = $invoice->items()->sum('total');
        $invoice->update(['total_amount' => $total]);
        $this->updateStatus($invoice->fresh());
    }

    /**
     * Recalcule le montant payé depuis les versements et met à jour le statut.
     */
    public function recalculatePaidAmount(Invoice $invoice): void
    {
        $paid = $invoice->payments()->sum('amount');
        $invoice->update(['paid_amount' => $paid]);
        $this->updateStatus($invoice->fresh());
    }

    /**
     * Met à jour le statut de la facture selon les montants.
     * draft    → pas encore émise
     * issued   → émise, rien payé
     * partial  → partiellement payée
     * paid     → soldée
     * cancelled → annulée manuellement
     */
    public function updateStatus(Invoice $invoice): void
    {
        if ($invoice->status === 'cancelled') return;

        $remaining = $invoice->remaining_amount;

        $status = match(true) {
            $invoice->status === 'draft'         => 'draft',
            $remaining <= 0                      => 'paid',
            $invoice->paid_amount > 0            => 'partial',
            default                              => 'issued',
        };

        $invoice->update(['status' => $status]);
    }

    /**
     * Génère un numéro de facture unique au format INV-YYYY-XXXX.
     */
    private function generateNumber(): string
    {
        $year  = now()->year;
        $count = Invoice::whereYear('created_at', $year)->count() + 1;
        return sprintf('INV-%d-%04d', $year, $count);
    }
}

// ─── Helper global ─────────────────────────────────────────────────────────────

if (! function_exists('setting')) {
    /**
     * Accès rapide aux settings depuis n'importe où dans l'app.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\Setting::get($key, $default);
    }
}
