<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();   // male | female | other
            $table->text('address')->nullable();
            $table->string('blood_type', 5)->nullable(); // A+, O-, etc.
            $table->text('allergies')->nullable();
            $table->text('notes')->nullable();      // notes internes du cabinet
            $table->timestamps();
 
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
