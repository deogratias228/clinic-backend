<?php

namespace App\Services;

use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service de gestion des créneaux horaires.
 * Calcule les créneaux disponibles en croisant :
 *   - les horaires récurrents du médecin (schedules)
 *   - les indisponibilités ponctuelles (unavailabilities)
 *   - les rendez-vous déjà pris (appointments non annulés)
 */
class SlotService
{
    /**
     * Retourne les créneaux disponibles pour un médecin à une date donnée.
     *
     * @return Collection<int, array{start: string, end: string}>
     */
    public function getAvailableSlots(Doctor $doctor, string $date): Collection
    {
        $carbon  = Carbon::parse($date);
        $dayName = strtolower($carbon->englishDayOfWeek);

        $schedule = $doctor->schedules()
            ->where('day_of_week', $dayName)
            ->where('is_active', true)
            ->first();

        if (! $schedule) {
            return collect();
        }

        // Journée entière bloquée — retourner immédiatement
        $fullDayBlocked = $doctor->unavailabilities()
            ->where('date', $date)
            ->whereNull('start_time')
            ->whereNull('end_time')
            ->exists();

        if ($fullDayBlocked) {
            return collect();
        }

        $all          = $this->generateSlots($date, $schedule->start_time, $schedule->end_time, $doctor->slot_duration_minutes);
        $booked       = $this->getBookedSlots($doctor, $date);
        $partialBlocks = $this->getPartialUnavailabilities($doctor, $date);

        return $all->filter(function (array $slot) use ($booked, $partialBlocks) {
            return ! $this->overlapsAny($slot, $booked)
                && ! $this->overlapsAny($slot, $partialBlocks);
        })->values();
    }

    /**
     * Retourne les dates disponibles pour un médecin sur une plage.
     * Une date est disponible si au moins un créneau y est libre.
     *
     * @return Collection<int, string> Dates au format Y-m-d
     */
    public function getAvailableDates(Doctor $doctor, string $from, string $to): Collection
    {
        $activeDays = $doctor->schedules()
            ->where('is_active', true)
            ->pluck('day_of_week')
            ->toArray();

        $dates  = collect();
        $cursor = Carbon::parse($from);
        $end    = Carbon::parse($to);

        while ($cursor->lte($end)) {
            $dayName = strtolower($cursor->englishDayOfWeek);

            if (in_array($dayName, $activeDays)) {
                if ($this->getAvailableSlots($doctor, $cursor->toDateString())->isNotEmpty()) {
                    $dates->push($cursor->toDateString());
                }
            }

            $cursor->addDay();
        }

        return $dates;
    }

    /**
     * Génère tous les créneaux théoriques entre start_time et end_time.
     *
     * @return Collection<int, array{start: string, end: string}>
     */
    private function generateSlots(string $date, string $startTime, string $endTime, int $durationMinutes): Collection
    {
        $slots   = collect();
        $current = Carbon::parse("{$date} {$startTime}");
        $end     = Carbon::parse("{$date} {$endTime}");

        while ($current->copy()->addMinutes($durationMinutes)->lte($end)) {
            $slotEnd = $current->copy()->addMinutes($durationMinutes);
            $slots->push([
                'start' => $current->format('H:i'),
                'end'   => $slotEnd->format('H:i'),
            ]);
            $current->addMinutes($durationMinutes);
        }

        return $slots;
    }

    /**
     * Retourne les créneaux déjà réservés (rendez-vous non annulés).
     *
     * @return Collection<int, array{start: string, end: string}>
     */
    private function getBookedSlots(Doctor $doctor, string $date): Collection
    {
        return $doctor->appointments()
            ->where('appointment_date', $date)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->get()
            ->map(fn ($a) => [
                'start' => substr($a->start_time, 0, 5),
                'end'   => substr($a->end_time, 0, 5),
            ]);
    }

    /**
     * Retourne les indisponibilités partielles (avec horaires) pour une date.
     *
     * @return Collection<int, array{start: string, end: string}>
     */
    private function getPartialUnavailabilities(Doctor $doctor, string $date): Collection
    {
        return $doctor->unavailabilities()
            ->where('date', $date)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->get()
            ->map(fn ($u) => [
                'start' => substr($u->start_time, 0, 5),
                'end'   => substr($u->end_time, 0, 5),
            ]);
    }

    /**
     * Vérifie si un créneau chevauche l'un des blocs existants.
     */
    private function overlapsAny(array $slot, Collection $blocks): bool
    {
        return $blocks->contains(
            fn ($block) => $slot['start'] < $block['end'] && $slot['end'] > $block['start']
        );
    }
}
