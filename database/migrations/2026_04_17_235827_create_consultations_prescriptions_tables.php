<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Consultations médicales et prescriptions.
     * Module optionnel : actif uniquement si le médecin a uses_consultation_module=true.
     * Une visite peut ne pas avoir de consultation enregistrée dans l'outil
     * même si le module est actif — le médecin peut préférer son carnet.
     */
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('doctor_id')->constrained()->cascadeOnDelete();
            $table->text('symptoms')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();  // traitement prescrit (texte libre)
            $table->text('notes')->nullable();       // notes privées du médecin
            $table->string('follow_up')->nullable(); // délai de suivi : "1 semaine", "1 mois"
            $table->timestamp('consulted_at');
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('consultation_id')->constrained()->cascadeOnDelete();
            $table->text('content');                 // contenu libre de l'ordonnance
            $table->date('valid_until')->nullable();
            $table->boolean('is_printed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('consultations');
    }
};
