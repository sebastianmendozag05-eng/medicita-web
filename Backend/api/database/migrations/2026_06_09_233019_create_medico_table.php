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
        Schema::create('medico', function (Blueprint $table) {
            $table->id('medId');
            $table->string('medNombre', 50);
            $table->string('medApePat', 50);
            $table->string('medApeMat', 50)->nullable();
            $table->string('medSexo', 10);
            $table->integer('medEdad');
            $table->string('medCorreo', 80);
            $table->string('medCedula', 20)->unique();
            $table->integer('medEstatus');
            $table->timestamp('medFechaReg');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medico');
    }
};
