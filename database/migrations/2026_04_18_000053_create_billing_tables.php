<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Module facturation complet.
 *
 * act_catalog   : catalogue des actes et tarifs configurables par l'admin.
 * invoices      : facture liée à une visite. status : draft | issued | paid | partial | cancelled.
 * invoice_items : lignes de facturation (acte du catalogue ou ligne manuelle).
 * payments      : versements sur une facture (plusieurs versements possibles, multi-modes).
 *                 method : cash | mobile_money | card | insurance | transfer.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('act_catalog', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name');
            $table->string('category')->nullable();     // consultation, imagerie, biologie...
            $table->decimal('default_price', 10, 2)->default(0);
            $table->string('currency', 5)->default('XOF');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();         // INV-2024-0001 — généré par le service
            $table->string('status')->default('draft'); // draft | issued | paid | partial | cancelled
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('currency', 5)->default('XOF');
            $table->text('notes')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'patient_id']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('act_catalog_id')->nullable()->constrained('act_catalog')->nullOnDelete();
            $table->string('label');                    // copié depuis le catalogue ou saisi manuellement
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);            // quantity * unit_price
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained(); // caissier qui a encaissé
            $table->decimal('amount', 10, 2);
            $table->string('method');                   // cash | mobile_money | card | insurance | transfer
            $table->string('reference')->nullable();    // numéro de transaction mobile money, etc.
            $table->text('notes')->nullable();
            $table->timestamp('paid_at');
            $table->timestamps();

            $table->index(['invoice_id', 'method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('act_catalog');
    }
};