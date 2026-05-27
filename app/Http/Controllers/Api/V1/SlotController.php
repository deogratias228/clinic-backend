<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Services\SlotService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Créneaux horaires disponibles.
 * Routes publiques — appelées par le booking flow côté patient.
 */
class SlotController extends Controller
{
    public function __construct(private readonly SlotService $slotService)
    {
    }

    /**
     * Retourne les créneaux disponibles pour un médecin à une date donnée.
     */
    public function byDate(Request $request, Doctor $doctor): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        abort_if(
            !$doctor->is_active || !$doctor->accepts_online_booking,
            422,
            'Ce médecin n\'accepte pas les rendez-vous en ligne.'
        );

        $slots = $this->slotService->getAvailableSlots($doctor, $request->date);

        return response()->json(['data' => $slots]);
    }

    /**
     * Retourne les dates disponibles sur une plage (pour le calendrier du booking).
     * Limité à 60 jours max pour éviter les requêtes abusives.
     */
    public function availableDates(Request $request, Doctor $doctor): JsonResponse
    {
        Log::info('Recherche de créneaux disponibles', [
            'doctor_id' => $doctor->id,
            'from' => $request->from,
            'to' => $request->to,
        ]);

        $validator = Validator::make($request->all(), [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            Log::warning('Validation échouée pour la recherche de créneaux', [
                'doctor_id' => $doctor->id,
                'errors' => $validator->errors()->all(),
            ]);

            return response()->json(['errors' => $validator->errors()], 422);
        }

        $from = $request->from ?? Carbon::today()->toDateString();
        $to = $request->to ?? Carbon::today()->addDays(30)->toDateString();

        abort_if(
            Carbon::parse($from)->diffInDays(Carbon::parse($to)) > 60,
            422,
            'La plage ne peut pas dépasser 60 jours.'
        );

        abort_if(
            !$doctor->is_active || !$doctor->accepts_online_booking,
            422,
            'Ce médecin n\'accepte pas les rendez-vous en ligne.'
        );

        $dates = $this->slotService->getAvailableDates($doctor, $from, $to);

        return response()->json(['data' => $dates]);
    }
}
