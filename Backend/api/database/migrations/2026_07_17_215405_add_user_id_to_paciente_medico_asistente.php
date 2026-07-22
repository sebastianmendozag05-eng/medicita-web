<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['paciente' => 'pacCorreo', 'medico' => 'medCorreo', 'asistente_medico' => 'astCorreo'] as $table => $correoCol) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('user_id')->nullable()->after($t->getTable() === 'paciente' ? 'pacId' : ($t->getTable() === 'medico' ? 'medId' : 'astId'))
                  ->constrained('users')->nullOnDelete();
            });

            // Backfill: vincular registros existentes cuyo correo coincida con users.email
            DB::statement("
                UPDATE `$table` t
                JOIN users u ON u.email = t.`$correoCol`
                SET t.user_id = u.id
                WHERE t.user_id IS NULL
            ");
        }
    }

    public function down(): void
    {
        foreach (['paciente', 'medico', 'asistente_medico'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropConstrainedForeignId('user_id');
            });
        }
    }
};
