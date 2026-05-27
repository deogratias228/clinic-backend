<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Visit;

/**
 * Service de gestion des visites.
 * Gère la création depuis un RDV ou en walk-in,
 * et les transitions de statut selon le flux configuré du cabinet.
 *
 * Flux complet  : registered → awaiting_payment → paid → in_consultation → done
 * Flux simplifié: registered → in_consultation → done
 * (configuré via setting 'visit.skip_payment_step')
 */
class VisitService
{
    /**
     * Crée une visite depuis un rendez-vous existant.
     * Met à jour le statut du RDV en 'done'.
     */
    public function createFromAppointment(Appointment $appointment, Patient $patient): Visit
    {
        $appointment->update(['status' => 'done', 'patient_id' => $patient->id]);

        return Visit::create([
            'appointment_id' => $appointment->id,
            'doctor_id'      => $appointment->doctor_id,
            'patient_id'     => $patient->id,
            'status'         => 'registered',
            'reason'         => $appointment->reason,
            'arrived_at'     => now(),
        ]);
    }

    /**
     * Crée une visite directe sans rendez-vous préalable (walk-in).
     */
    public function createWalkIn(array $data): Visit
    {
        return Visit::create([
            'appointment_id' => null,
            'doctor_id'      => $data['doctor_id'],
            'patient_id'     => $data['patient_id'],
            'status'         => 'registered',
            'reason'         => $data['reason'] ?? null,
            'arrived_at'     => now(),
        ]);
    }

    /**
     * Avance la visite vers l'étape suivante selon le flux configuré.
     * Retourne le nouveau statut ou lance une exception si la transition est invalide.
     */
    public function advance(Visit $visit): Visit
    {
        $skipPayment = filter_var(
            \App\Models\Setting::get('visit.skip_payment_step', false),
            FILTER_VALIDATE_BOOLEAN
        );

        $transitions = $skipPayment
            ? $this->simpleFlowTransitions()
            : $this->fullFlowTransitions();

        $currentStatus = $visit->status;

        abort_unless(
            isset($transitions[$currentStatus]),
            422,
            "La visite ne peut pas avancer depuis le statut : {$currentStatus}"
        );

        $nextStatus = $transitions[$currentStatus];
        $updates    = ['status' => $nextStatus];

        // Horodatage automatique selon le statut cible
        if ($nextStatus === 'in_consultation') $updates['seen_at'] = now();
        if ($nextStatus === 'done')            $updates['done_at'] = now();

        $visit->update($updates);

        return $visit->fresh();
    }

    /**
     * Flux complet : tous les cabinets avec étape de paiement distincte.
     */
    private function fullFlowTransitions(): array
    {
        return [
            'registered'        => 'awaiting_payment',
            'awaiting_payment'  => 'paid',
            'paid'              => 'in_consultation',
            'in_consultation'   => 'done',
        ];
    }

    /**
     * Flux simplifié : cabinets où la secrétaire gère RDV et paiement ensemble.
     */
    private function simpleFlowTransitions(): array
    {
        return [
            'registered'      => 'in_consultation',
            'in_consultation' => 'done',
        ];
    }
}
