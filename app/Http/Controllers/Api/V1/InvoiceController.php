<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceItemRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Visit;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des factures.
 * Une facture est liée à une visite et peut avoir plusieurs lignes et paiements.
 * Le statut est recalculé automatiquement après chaque modification.
 */
class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    /**
     * Liste des factures avec filtres.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status'     => ['nullable', 'string'],
            'patient_id' => ['nullable', 'uuid'],
            'date_from'  => ['nullable', 'date_format:Y-m-d'],
            'date_to'    => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'per_page'   => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $query = Invoice::with('patient', 'visit.doctor')
            ->orderByDesc('created_at');

        if ($request->status)     $query->where('status', $request->status);
        if ($request->patient_id) $query->where('patient_id', $request->patient_id);
        if ($request->date_from)  $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to)    $query->whereDate('created_at', '<=', $request->date_to);

        $invoices = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => InvoiceResource::collection($invoices),
            'meta' => [
                'total'        => $invoices->total(),
                'current_page' => $invoices->currentPage(),
                'last_page'    => $invoices->lastPage(),
            ],
        ]);
    }

    /**
     * Détail d'une facture avec ses lignes et paiements.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load('patient', 'visit.doctor', 'items.actCatalog', 'payments.processedBy');

        return response()->json(['data' => InvoiceResource::make($invoice)]);
    }

    /**
     * Crée une facture draft pour une visite.
     */
    public function store(Request $request, Visit $visit): JsonResponse
    {
        abort_if($visit->invoice()->exists(), 422, 'Une facture existe déjà pour cette visite.');

        $invoice = $this->invoiceService->createForVisit($visit);

        return response()->json(['data' => InvoiceResource::make($invoice)], 201);
    }

    /**
     * Met à jour les métadonnées d'une facture (remise, notes, statut manuel).
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        abort_if($invoice->status === 'cancelled', 422, 'Une facture annulée ne peut pas être modifiée.');

        $invoice->update($request->validated());
        $this->invoiceService->recalculatePaidAmount($invoice->fresh());

        return response()->json(['data' => InvoiceResource::make($invoice->fresh()->load('items', 'payments'))]);
    }

    /**
     * Émet une facture (passe de draft à issued).
     */
    public function issue(Invoice $invoice): JsonResponse
    {
        abort_if($invoice->status !== 'draft', 422, 'Seule une facture en brouillon peut être émise.');
        abort_if($invoice->items()->count() === 0, 422, 'La facture doit contenir au moins une ligne.');

        $invoice->update([
            'status'    => 'issued',
            'issued_at' => now(),
        ]);

        return response()->json(['data' => InvoiceResource::make($invoice->load('items', 'payments'))]);
    }

    /**
     * Annule une facture.
     */
    public function cancel(Invoice $invoice): JsonResponse
    {
        abort_if($invoice->status === 'paid', 422, 'Une facture soldée ne peut pas être annulée.');

        $invoice->update(['status' => 'cancelled']);

        return response()->json(['data' => InvoiceResource::make($invoice)]);
    }

    /**
     * Ajoute une ligne à une facture.
     */
    public function addItem(StoreInvoiceItemRequest $request, Invoice $invoice): JsonResponse
    {
        abort_if($invoice->status === 'cancelled', 422, 'Impossible de modifier une facture annulée.');
        abort_if($invoice->status === 'paid', 422, 'Impossible de modifier une facture soldée.');

        $item = $this->invoiceService->addItem($invoice, $request->validated());

        return response()->json([
            'data' => InvoiceResource::make($invoice->fresh()->load('items', 'payments')),
        ], 201);
    }

    /**
     * Supprime une ligne de facture.
     */
    public function removeItem(Invoice $invoice, InvoiceItem $item): JsonResponse
    {
        abort_if($invoice->status === 'cancelled', 422, 'Impossible de modifier une facture annulée.');
        abort_if($item->invoice_id !== $invoice->id, 403, 'Cette ligne n\'appartient pas à cette facture.');

        $this->invoiceService->removeItem($invoice, $item);

        return response()->json([
            'data' => InvoiceResource::make($invoice->fresh()->load('items', 'payments')),
        ]);
    }
}
