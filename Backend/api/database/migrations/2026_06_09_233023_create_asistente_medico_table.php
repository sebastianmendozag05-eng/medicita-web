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
        Schema::create('asistente_medico', function (Blueprint $table) {
            $table->id('astId');
            $table->string('astNombre', 100);
            $table->string('astApePat', 50);
            $table->string('astApeMat', 50)->nullable();
            $table->integer('astEdad');
            $table->string('astCorreo', 80);
            $table->string('astTelefono', 20);
            $table->integer('astEstatus');
            $table->timestamp('astFechaReg');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistente_medico');
    }
};
