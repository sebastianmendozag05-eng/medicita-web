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
        Schema::create('cita', function (Blueprint $table) {
            $table->id('citId');
            $table->unsignedBigInteger('medId');
            $table->unsignedBigInteger('pacId');
            $table->unsignedBigInteger('astId')->nullable();
            $table->timestamp('citFecha');
            $table->time('citHora');
            $table->string('citMotivo', 200);
            $table->enum('citEstatus', ['agendada', 'confirmada', 'completada', 'cancelada'])->default('agendada');
            $table->timestamp('citFechaReg');
            $table->string('citMotivoCancela', 255)->nullable();
            $table->foreign('medId')->references('medId')->on('medico');
            $table->foreign('pacId')->references('pacId')->on('paciente');
            $table->foreign('astId')->references('astId')->on('asistente_medico');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cita');
    }
};
