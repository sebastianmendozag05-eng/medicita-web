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
        Schema::create('paciente', function (Blueprint $table) {
            $table->id('pacId');
            $table->string('pacNombre', 50);
            $table->string('pacApePat', 50);
            $table->string('pacApeMat', 50)->nullable();
            $table->string('pacSexo', 50);
            $table->date('pacFechaNac');
            $table->string('pacNSS', 20);
            $table->string('pacCorreo', 80);
            $table->string('pacTelefono', 20);
            $table->float('pacPeso');
            $table->float('pacEstatura');
            $table->integer('pacEstatus');
            $table->timestamp('pacFechaReg');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paciente');
    }
};
