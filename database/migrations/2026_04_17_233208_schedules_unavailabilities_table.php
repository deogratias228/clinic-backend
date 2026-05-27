<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Horaires récurrents et indisponibilités ponctuelles des médecins.
 * day_of_week stocké en string : monday, tuesday... (pas d'enum colonne).
 * start_time/end_time null sur unavailabilities = indisponibilité journée entière.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('day_of_week');  // monday | tuesday | ... | sunday
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['doctor_id', 'day_of_week', 'start_time']);
        });

        Schema::create('unavailabilities', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('doctor_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable(); // null = journée entière
            $table->time('end_time')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['doctor_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unavailabilities');
        Schema::dropIfExists('schedules');
    }
};