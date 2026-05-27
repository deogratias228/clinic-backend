<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spécialités médicales et médecins du cabinet.
 * Un médecin peut être lié à un compte utilisateur (pour accès back-office)
 * ou exister uniquement comme praticien sans accès à l'outil.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialties', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name');
            $table->string('color', 7)->nullable(); // couleur hex pour l'interface
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('doctors', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete(); // compte optionnel
            $table->foreignUuid('specialty_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('photo_path')->nullable();
            $table->text('bio')->nullable();
            $table->integer('slot_duration_minutes')->default(30);
            $table->boolean('accepts_online_booking')->default(true);
            $table->boolean('uses_consultation_module')->default(false); // optionnel par médecin
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('specialties');
    }
};