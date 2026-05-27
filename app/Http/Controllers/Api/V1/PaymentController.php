<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Enregistrement des paiements sur les factures.
 * Un paiement est toujours tracé avec le caissier qui l'a enregistré.
 * Plusieurs versements de méthodes différentes sont possibles sur une même facture.
 */
class PaymentController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    /**
     * Enregistre un paiement sur une facture.
     * Met à jour automatiquement le statut de la facture et de la visite.
     */
    public function store(StorePaymentRequest $request, Invoice $invoice): JsonResponse
    {
        abort_if($invoice->status === 'cancelled', 422, 'Impossible d\'encaisser sur une facture annulée.');
        abort_if($invoice->status === 'paid',      422, 'Cette facture est déjà soldée.');
        abort_if($invoice->status === 'draft',     422, 'Émettez la facture avant d\'enregistrer un paiement.');

        $data = $request->validated();

        // Vérifier que le montant ne dépasse pas le restant dû
        abort_if(
            $data['amount'] > $invoice->remaining_amount,
            422,
            sprintf(
                'Le montant saisi (%.2f) dépasse le restant dû (%.2f %s).',
                $data['amount'],
                $invoice->remaining_amount,
                $invoice->currency
            )
        );

        $payment = $this->invoiceService->addPayment($invoice, array_merge(
            $data,
            ['user_id' => $request->user()->id]
        ));

        // Si la facture est soldée, marquer la visite comme payée
        $invoice->refresh();
        if ($invoice->status === 'paid' && $invoice->visit) {
            $visit = $invoice->visit;
            if ($visit->status === 'awaiting_payment') {
                $visit->update(['status' => 'paid']);
            }
        }

        return response()->json([
            'data'    => PaymentResource::make($payment),
            'invoice' => [
                'status'           => $invoice->status,
                'paid_amount'      => $invoice->paid_amount,
                'remaining_amount' => $invoice->remaining_amount,
            ],
        ], 201);
    }

    /**
     * Annule un paiement (correction d'erreur de caisse).
     * Réservé aux administrateurs uniquement.
     */
    public function destroy(Request $request, Payment $payment): JsonResponse
    {
        abort_if(
            ! $request->user()->isSuperAdmin(),
            403,
            'Seul un administrateur peut annuler un paiement.'
        );

        $invoice = $payment->invoice;
        $payment->delete();

        $this->invoiceService->recalculatePaidAmount($invoice);

        return response()->json(null, 204);
    }
}
