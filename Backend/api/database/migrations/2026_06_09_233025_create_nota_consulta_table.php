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
        Schema::create('nota_consulta', function (Blueprint $table) {
            $table->id('notaId');
            $table->unsignedBigInteger('citId')->unique();
            $table->unsignedBigInteger('medId');
            $table->unsignedBigInteger('pacId');
            $table->boolean('notaAsistencia')->default(true);
            $table->text('notaDiagnostico');
            $table->text('notaReceta')->nullable();
            $table->timestamp('notaFechaCreacion')->useCurrent();
            $table->foreign('citId')->references('citId')->on('cita');
            $table->foreign('medId')->references('medId')->on('medico');
            $table->foreign('pacId')->references('pacId')->on('paciente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_consulta');
    }
};
