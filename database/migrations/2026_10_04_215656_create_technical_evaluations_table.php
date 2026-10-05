<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('technical_evaluations', function (Blueprint $table) {
            $table->id();

            $table->string('nom_fournisseur');

            $table->string('cahier_des_charges_path');
            $table->string('offre_fournisseur_path');

            $table->decimal('note_finale', 10, 2)->default(0);
            $table->decimal('note_maximale', 10, 2)->default(0);
            $table->decimal('pourcentage', 5, 2)->default(0);

            $table->string('statut')->default('EN COURS');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_evaluations');
    }
};
