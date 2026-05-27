<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\SyncSchedulesRequest;
use App\Http\Requests\Schedule\StoreUnavailabilityRequest;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\UnavailabilityResource;
use App\Models\Doctor;
use App\Models\Schedule;
use App\Models\Unavailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des horaires récurrents et indisponibilités des médecins.
 */
class ScheduleController extends Controller
{
    /**
     * Retourne les horaires d'un médecin.
     */
    public function index(Doctor $doctor): JsonResponse
    {
        return response()->json([
            'data' => ScheduleResource::collection(
                $doctor->schedules()->orderBy('day_of_week')->get()
            ),
        ]);
    }

    /**
     * Remplace tous les horaires d'un médecin en une opération (sync).
     * Supprime les anciens et insère les nouveaux.
     */
    public function sync(SyncSchedulesRequest $request, Doctor $doctor): JsonResponse
    {
        $doctor->schedules()->delete();

        $schedules = collect($request->validated('schedules'))
            ->map(fn ($item) => array_merge($item, [
                'doctor_id'  => $doctor->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

        Schedule::insert($schedules->toArray());

        return response()->json([
            'data' => ScheduleResource::collection(
                $doctor->schedules()->orderBy('day_of_week')->get()
            ),
        ]);
    }

    /**
     * Liste les indisponibilités d'un médecin sur une plage de dates.
     */
    public function unavailabilities(Request $request, Doctor $doctor): JsonResponse
    {
        $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to'   => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $query = $doctor->unavailabilities()->orderBy('date');

        if ($request->from) $query->where('date', '>=', $request->from);
        if ($request->to)   $query->where('date', '<=', $request->to);

        return response()->json(['data' => UnavailabilityResource::collection($query->get())]);
    }

    /**
     * Ajoute une indisponibilité ponctuelle.
     */
    public function storeUnavailability(StoreUnavailabilityRequest $request, Doctor $doctor): JsonResponse
    {
        $unavailability = $doctor->unavailabilities()->create($request->validated());

        return response()->json(['data' => UnavailabilityResource::make($unavailability)], 201);
    }

    /**
     * Supprime une indisponibilité.
     */
    public function destroyUnavailability(Unavailability $unavailability): JsonResponse
    {
        $unavailability->delete();

        return response()->json(null, 204);
    }
}
