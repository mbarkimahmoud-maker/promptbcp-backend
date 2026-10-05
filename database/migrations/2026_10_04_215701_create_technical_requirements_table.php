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
        Schema::create('technical_requirements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('technical_evaluation_id')
                ->constrained('technical_evaluations')
                ->cascadeOnDelete();

            $table->text('description');

            $table->text('valeur_demandee')->nullable();

            $table->boolean('obligatoire')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_requirements');
    }
};
