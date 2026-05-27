<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultation\StoreConsultationRequest;
use App\Http\Requests\Consultation\UpdateConsultationRequest;
use App\Http\Requests\Consultation\StorePrescriptionRequest;
use App\Http\Resources\ConsultationResource;
use App\Http\Resources\PrescriptionResource;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Consultations médicales et prescriptions.
 * Accessible uniquement aux médecins ayant uses_consultation_module=true.
 * Un médecin ne peut accéder qu'à ses propres consultations.
 */
class ConsultationController extends Controller
{
    /**
     * Crée une consultation pour une visite.
     * Vérifie que le médecin utilise bien le module consultation.
     */
    public function store(StoreConsultationRequest $request, Visit $visit): JsonResponse
    {
        abort_if($visit->consultation()->exists(), 422, 'Une consultation existe déjà pour cette visite.');

        abort_if(
            !$visit->doctor->uses_consultation_module,
            422,
            'Ce médecin n\'utilise pas le module de consultation.'
        );

        $consultation = Consultation::create(array_merge(
            $request->validated(),
            [
                'visit_id' => $visit->id,
                'doctor_id' => $visit->doctor_id,
                'consulted_at' => now(),
            ]
        ));

        return response()->json([
            'data' => ConsultationResource::make($consultation->load('prescriptions')),
        ], 201);
    }

    /**
     * Met à jour une consultation existante.
     * Bloque si la visite est terminée depuis plus de 24h (protection des données médicales).
     */
    public function update(UpdateConsultationRequest $request, Consultation $consultation): JsonResponse
    {
        $lockedAt = $consultation->visit->done_at;

        abort_if(
            $lockedAt && $lockedAt->diffInHours(now()) > 24,
            422,
            'Cette consultation ne peut plus être modifiée (verrouillée après 24h).'
        );

        $consultation->update($request->validated());

        return response()->json([
            'data' => ConsultationResource::make($consultation->load('prescriptions')),
        ]);
    }

    /**
     * Retourne la consultation d'une visite.
     */
    public function showByVisit(Visit $visit): JsonResponse
    {
        $consultation = $visit->consultation()->with('prescriptions', 'doctor')->firstOrFail();

        return response()->json(['data' => ConsultationResource::make($consultation)]);
    }

    /**
     * Ajoute une prescription à une consultation.
     */
    public function storePrescription(StorePrescriptionRequest $request, Consultation $consultation): JsonResponse
    {
        $prescription = $consultation->prescriptions()->create($request->validated());

        return response()->json([
            'data' => PrescriptionResource::make($prescription),
        ], 201);
    }

    /**
     * Marque une prescription comme imprimée.
     */
    public function markPrinted(Prescription $prescription): JsonResponse
    {
        $prescription->update(['is_printed' => true]);

        return response()->json(['data' => PrescriptionResource::make($prescription)]);
    }

    /**
     * Supprime une prescription.
     */
    public function destroyPrescription(Prescription $prescription): JsonResponse
    {
        $prescription->delete();

        return response()->json(null, 204);
    }
}
