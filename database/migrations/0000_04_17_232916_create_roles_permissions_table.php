<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tables du système RBAC.
     * Un utilisateur hérite des permissions de son rôle.
     * user_permissions permet de surcharger individuellement (accorder ou retirer).
     */
    public function up(): void
    {
        // Rôles : prédéfinis à l'installation, modifiables par l'admin
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name')->unique();   // slug machine : super_admin, secretaire, comptable...
            $table->string('label');            // libellé affiché : "Super administrateur"
            $table->boolean('is_default')->default(false); // rôle proposé à la création d'un user
            $table->boolean('is_system')->default(false);  // rôle non supprimable (super_admin)
            $table->timestamps();
        });

        // Permissions atomiques par module
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name')->unique();   // slug : appointments.create, billing.view...
            $table->string('module');           // appointments, billing, consultations, reports...
            $table->string('label');            // "Créer un rendez-vous"
            $table->timestamps();
        });

        // Pivot rôle <-> permissions
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignUuid('role_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
