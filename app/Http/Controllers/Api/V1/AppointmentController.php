<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\BookAppointmentRequest;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\SlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des rendez-vous.
 * Routes publiques : prise de RDV en ligne par le patient (sans compte).
 * Routes admin    : CRUD complet + filtres pour l'agenda du cabinet.
 */
class AppointmentController extends Controller
{
    public function __construct(private readonly SlotService $slotService)
    {
    }

    /**
     * Prise de rendez-vous en ligne par un patient.
     * Vérifie la disponibilité du créneau au moment de la soumission.
     */
    public function book(BookAppointmentRequest $request, Doctor $doctor): JsonResponse
    {
        $data = $request->validated();

        abort_if(
            !$doctor->is_active || !$doctor->accepts_online_booking,
            422,
            'Ce médecin n\'accepte pas les rendez-vous en ligne.'
        );

        // Vérification de disponibilité au moment de la soumission (anti-conflit)
        $available = $this->slotService->getAvailableSlots($doctor, $data['appointment_date']);

        $slotFree = $available->contains(fn($s) => $s['start'] === $data['start_time']);

        if (!$slotFree) {
            return response()->json([
                'message' => 'Ce créneau n\'est plus disponible.',
                'errors' => ['start_time' => ['Créneau indisponible, veuillez en choisir un autre.']],
            ], 422);
        }

        $appointment = Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => null,
            'appointment_date' => $data['appointment_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'status' => 'pending',
            'source' => 'online',
            'patient_name' => $data['patient_name'],
            'patient_phone' => $data['patient_phone'],
            'reason' => $data['reason'] ?? null,
        ]);

        return response()->json([
            'data' => AppointmentResource::make($appointment->load('doctor.specialty')),
        ], 201);
    }

    /**
     * Liste des rendez-vous avec filtres pour l'agenda admin.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'doctor_id' => ['nullable', 'uuid'],
            'status' => ['nullable', 'string'],
            'source' => ['nullable', 'in:online,manual'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $query = Appointment::with('doctor.specialty', 'patient')
            ->orderBy('appointment_date')
            ->orderBy('start_time');

        if ($request->date)
            $query->forDate($request->date);
        if ($request->doctor_id)
            $query->where('doctor_id', $request->doctor_id);
        if ($request->status)
            $query->where('status', $request->status);
        if ($request->source)
            $query->where('source', $request->source);

        $appointments = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => AppointmentResource::collection($appointments),
            'meta' => [
                'total' => $appointments->total(),
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
            ],
        ]);
    }

    /**
     * Détail d'un rendez-vous.
     */
    public function show(Appointment $appointment): JsonResponse
    {
        $appointment->load('doctor.specialty', 'patient', 'visit');

        return response()->json(['data' => AppointmentResource::make($appointment)]);
    }

    /**
     * Crée un rendez-vous manuellement depuis le back-office.
     */
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $appointment = Appointment::create(array_merge($data, ['source' => 'manual']));

        return response()->json([
            'data' => AppointmentResource::make($appointment->load('doctor.specialty', 'patient')),
        ], 201);
    }

    /**
     * Met à jour un rendez-vous (statut, notes, horaire).
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $appointment->update($request->validated());

        return response()->json([
            'data' => AppointmentResource::make($appointment->load('doctor.specialty', 'patient')),
        ]);
    }

    /**
     * Supprime un rendez-vous.
     * En pratique on préfère annuler (status=cancelled) plutôt que supprimer.
     */
    public function destroy(Appointment $appointment): JsonResponse
    {
        $appointment->delete();

        return response()->json(null, 204);
    }
}
