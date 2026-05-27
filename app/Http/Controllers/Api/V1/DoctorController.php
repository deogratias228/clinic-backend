<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreDoctorRequest;
use App\Http\Requests\Doctor\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Gestion des médecins du cabinet.
 * Routes publiques : liste des médecins actifs acceptant la prise de RDV en ligne.
 * Routes admin    : CRUD complet avec gestion de la photo.
 */
class DoctorController extends Controller
{
    /**
     * Liste des médecins actifs pour le site public.
     * Filtrés sur accepts_online_booking pour le booking en ligne.
     */
    public function index(): JsonResponse
    {
        $doctors = Doctor::with('specialty')
            ->where('is_active', true)
            ->where('accepts_online_booking', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => DoctorResource::collection($doctors)]);
    }

    /**
     * Liste complète des médecins pour l'admin (actifs et inactifs).
     */
    public function adminIndex(): JsonResponse
    {
        $doctors = Doctor::with('specialty', 'user')
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => DoctorResource::collection($doctors)]);
    }

    /**
     * Détail d'un médecin avec ses horaires.
     */
    public function show(Doctor $doctor): JsonResponse
    {
        $doctor->load('specialty', 'schedules', 'user');

        return response()->json(['data' => DoctorResource::make($doctor)]);
    }

    /**
     * Crée un nouveau médecin.
     */
    public function store(StoreDoctorRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('doctors', 'public');
        }

        $doctor = Doctor::create($data);

        return response()->json(['data' => DoctorResource::make($doctor->load('specialty'))], 201);
    }

    /**
     * Met à jour un médecin existant.
     */
    public function update(UpdateDoctorRequest $request, Doctor $doctor): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($doctor->photo_path) {
                Storage::disk('public')->delete($doctor->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('doctors', 'public');
        }

        $doctor->update($data);

        return response()->json(['data' => DoctorResource::make($doctor->load('specialty'))]);
    }

    /**
     * Supprime un médecin.
     * Bloque la suppression si le médecin a des rendez-vous à venir.
     */
    public function destroy(Doctor $doctor): JsonResponse
    {
        $hasFutureAppointments = $doctor->appointments()
            ->where('appointment_date', '>=', today())
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->exists();

        abort_if(
            $hasFutureAppointments,
            422,
            'Ce médecin a des rendez-vous à venir. Annulez-les avant de le supprimer.'
        );

        if ($doctor->photo_path) {
            Storage::disk('public')->delete($doctor->photo_path);
        }

        $doctor->delete();

        return response()->json(null, 204);
    }
}
