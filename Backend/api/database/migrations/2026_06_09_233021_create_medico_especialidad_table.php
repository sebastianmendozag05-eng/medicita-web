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
        Schema::create('medico_especialidad', function (Blueprint $table) {
            $table->id('meId');
            $table->unsignedBigInteger('medId');
            $table->unsignedBigInteger('espeId');
            $table->foreign('medId')->references('medId')->on('medico');
            $table->foreign('espeId')->references('espeId')->on('especialidad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medico_especialidad');
    }
};
