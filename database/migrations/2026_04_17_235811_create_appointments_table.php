<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rendez-vous — pris en ligne ou créés manuellement par le cabinet.
 * status en string : pending | confirmed | cancelled | no_show | done.
 * source en string : online | manual (permet les stats par canal).
 * patient_id nullable : un RDV peut être créé avant la fiche patient.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status')->default('pending');   // pending | confirmed | cancelled | no_show | done
            $table->string('source')->default('manual');    // online | manual
            $table->string('patient_name')->nullable();     // nom saisi en ligne (avant fiche patient)
            $table->string('patient_phone', 20)->nullable();
            $table->text('reason')->nullable();             // motif de la consultation
            $table->text('notes')->nullable();              // notes internes
            $table->timestamps();

            $table->index(['doctor_id', 'appointment_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};