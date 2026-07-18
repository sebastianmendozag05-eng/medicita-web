<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medico', function (Blueprint $table) {
            $table->string('medTelefono', 20)->nullable()->after('medCorreo');
        });
    }

    public function down(): void
    {
        Schema::table('medico', function (Blueprint $table) {
            $table->dropColumn('medTelefono');
        });
    }
};
