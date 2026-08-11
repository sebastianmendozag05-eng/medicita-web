<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq', function (Blueprint $table) {
            $table->id('faqId');
            $table->string('faqCategoria', 60); // citas | cuenta | pagos | general
            $table->string('faqPregunta', 200);
            $table->string('faqRespuesta', 800);
            $table->string('faqPalabrasClave', 300); // lista separada por comas, para el matching del chatbot
            $table->boolean('faqActivo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq');
    }
};
