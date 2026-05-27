<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Visites — représente le passage réel du patient au cabinet.
 * Découplé du rendez-vous : une visite peut exister sans RDV (walk-in),
 * un RDV peut ne jamais devenir une visite (absent).
 *
 * Flux configurable via status :
 *   registered → awaiting_payment → paid → in_consultation → done
 * Les cabinets simples passent directement de registered à in_consultation.
 *
 * arrived_at, seen_at, done_at permettent de mesurer les temps d'attente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('registered'); // registered | awaiting_payment | paid | in_consultation | done
            $table->text('reason')->nullable();              // motif saisi à l'accueil
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('seen_at')->nullable();        // quand le médecin commence
            $table->timestamp('done_at')->nullable();        // quand la consultation se termine
            $table->timestamps();

            $table->index(['doctor_id', 'status']);
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};