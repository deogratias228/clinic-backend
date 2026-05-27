<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des dossiers patients.
 * Recherche par nom ou téléphone pour retrouver rapidement un patient à l'accueil.
 */
class PatientController extends Controller
{
    /**
     * Liste et recherche des patients.
     * Paramètre search : recherche sur nom complet ou téléphone.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search'   => ['nullable', 'string', 'min:2'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $query = Patient::orderBy('last_name')->orderBy('first_name');

        if ($request->search) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", [$search])
                  ->orWhereRaw("CONCAT(last_name, ' ', first_name) ILIKE ?", [$search])
                  ->orWhere('phone', 'ILIKE', $search);
            });
        }

        $patients = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => PatientResource::collection($patients),
            'meta' => [
                'total'        => $patients->total(),
                'current_page' => $patients->currentPage(),
                'last_page'    => $patients->lastPage(),
            ],
        ]);
    }

    /**
     * Détail d'un patient avec son historique de visites.
     */
    public function show(Patient $patient): JsonResponse
    {
        $patient->load([
            'visits.doctor.specialty',
            'visits.consultation',
            'visits.invoice',
            'appointments' => fn ($q) => $q->orderByDesc('appointment_date')->limit(10),
        ]);

        return response()->json(['data' => PatientResource::make($patient)]);
    }

    /**
     * Crée un nouveau dossier patient.
     */
    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = Patient::create($request->validated());

        return response()->json(['data' => PatientResource::make($patient)], 201);
    }

    /**
     * Met à jour un dossier patient.
     */
    public function update(UpdatePatientRequest $request, Patient $patient): JsonResponse
    {
        $patient->update($request->validated());

        return response()->json(['data' => PatientResource::make($patient)]);
    }

    /**
     * Supprime un dossier patient.
     * Bloque si le patient a des factures non soldées.
     */
    public function destroy(Patient $patient): JsonResponse
    {
        $hasUnpaidInvoices = $patient->invoices()->unpaid()->exists();

        abort_if(
            $hasUnpaidInvoices,
            422,
            'Ce patient a des factures non soldées.'
        );

        $patient->delete();

        return response()->json(null, 204);
    }
}
