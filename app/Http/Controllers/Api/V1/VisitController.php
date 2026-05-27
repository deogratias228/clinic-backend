<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Visit\StoreVisitRequest;
use App\Http\Resources\VisitResource;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Visit;
use App\Services\VisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des visites patients.
 * Une visite est créée depuis un RDV existant ou directement (walk-in).
 * Les transitions de statut reflètent le flux configuré du cabinet.
 */
class VisitController extends Controller
{
    public function __construct(private readonly VisitService $visitService) {}

    /**
     * Liste des visites du jour avec statuts, pour l'écran d'accueil.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date'      => ['nullable', 'date_format:Y-m-d'],
            'doctor_id' => ['nullable', 'uuid'],
            'status'    => ['nullable', 'string'],
        ]);

        $date = $request->date ?? today()->toDateString();

        $query = Visit::with('patient', 'doctor.specialty', 'invoice', 'consultation')
            ->whereDate('arrived_at', $date)
            ->orderBy('arrived_at');

        if ($request->doctor_id) $query->where('doctor_id', $request->doctor_id);
        if ($request->status)    $query->where('status', $request->status);

        return response()->json(['data' => VisitResource::collection($query->get())]);
    }

    /**
     * Détail d'une visite.
     */
    public function show(Visit $visit): JsonResponse
    {
        $visit->load('patient', 'doctor.specialty', 'appointment', 'consultation.prescriptions', 'invoice.items', 'invoice.payments');

        return response()->json(['data' => VisitResource::make($visit)]);
    }

    /**
     * Crée une visite depuis un rendez-vous existant (patient se présente à l'accueil).
     * Associe ou crée le dossier patient depuis les infos du RDV.
     */
    public function createFromAppointment(Request $request, Appointment $appointment): JsonResponse
    {
        $request->validate([
            'patient_id' => ['nullable', 'uuid', 'exists:patients,id'],
        ]);

        abort_if($appointment->visit()->exists(), 422, 'Une visite existe déjà pour ce rendez-vous.');

        // Retrouver ou créer la fiche patient
        $patient = $request->patient_id
            ? Patient::findOrFail($request->patient_id)
            : Patient::firstOrCreate(
                ['phone' => $appointment->patient_phone],
                [
                    'first_name' => explode(' ', $appointment->patient_name)[0] ?? $appointment->patient_name,
                    'last_name'  => implode(' ', array_slice(explode(' ', $appointment->patient_name), 1)) ?: '-',
                    'phone'      => $appointment->patient_phone,
                ]
            );

        $visit = $this->visitService->createFromAppointment($appointment, $patient);

        return response()->json([
            'data' => VisitResource::make($visit->load('patient', 'doctor.specialty')),
        ], 201);
    }

    /**
     * Crée une visite directe sans rendez-vous (walk-in).
     */
    public function store(StoreVisitRequest $request): JsonResponse
    {
        $visit = $this->visitService->createWalkIn($request->validated());

        return response()->json([
            'data' => VisitResource::make($visit->load('patient', 'doctor.specialty')),
        ], 201);
    }

    /**
     * Avance la visite vers le statut suivant selon le flux du cabinet.
     */
    public function advance(Visit $visit): JsonResponse
    {
        $visit = $this->visitService->advance($visit);

        return response()->json([
            'data' => VisitResource::make($visit->load('patient', 'doctor.specialty')),
        ]);
    }

    /**
     * Revient au statut précédent (correction d'une erreur de manipulation).
     * Uniquement autorisé si la visite n'est pas terminée.
     */
    public function rollback(Visit $visit): JsonResponse
    {
        abort_if($visit->status === 'done', 422, 'Une visite terminée ne peut pas être modifiée.');

        $previous = [
            'awaiting_payment' => 'registered',
            'paid'             => 'awaiting_payment',
            'in_consultation'  => 'paid',
        ];

        abort_unless(isset($previous[$visit->status]), 422, 'Retour impossible depuis ce statut.');

        $visit->update(['status' => $previous[$visit->status]]);

        return response()->json([
            'data' => VisitResource::make($visit->load('patient', 'doctor.specialty')),
        ]);
    }
}
