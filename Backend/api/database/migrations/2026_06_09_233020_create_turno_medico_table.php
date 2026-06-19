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
        Schema::create('turno_medico', function (Blueprint $table) {
            $table->id('turId');
            $table->unsignedBigInteger('medId')->unique();
            $table->string('turDiasLaborales', 100);
            $table->time('turHoraEntrada');
            $table->time('turHoraSalida');
            $table->foreign('medId')->references('medId')->on('medico');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turno_medico');
    }
};
