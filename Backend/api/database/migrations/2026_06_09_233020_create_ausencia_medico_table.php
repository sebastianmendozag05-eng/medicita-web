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
        Schema::create('ausencia_medico', function (Blueprint $table) {
            $table->id('ausId');
            $table->unsignedBigInteger('medId');
            $table->enum('ausTipo', ['Descanso Semanal', 'Vacaciones', 'Incapacidad']);
            $table->date('ausFecha');
            $table->string('ausMotivo', 200)->nullable();
            $table->foreign('medId')->references('medId')->on('medico');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ausencia_medico');
    }
};
