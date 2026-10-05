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
        Schema::create('technical_evaluation_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('technical_requirement_id')
                ->constrained('technical_requirements')
                ->cascadeOnDelete();

            $table->text('valeur_offerte')->nullable();

            $table->boolean('conforme')->default(false);

            $table->decimal('points_obtenus', 10, 2)->default(0);

            $table->timestamps();

            $table->unique('technical_requirement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_evaluation_results');
    }
};
