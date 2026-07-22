<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('especialidad')->insert([
            ['espeNombre' => 'Medicina General', 'created_at' => now(), 'updated_at' => now()],
            ['espeNombre' => 'Pediatría',        'created_at' => now(), 'updated_at' => now()],
            ['espeNombre' => 'Cardiología',      'created_at' => now(), 'updated_at' => now()],
            ['espeNombre' => 'Ginecología',      'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::table('especialidad')->whereIn('espeNombre', [
            'Medicina General', 'Pediatría', 'Cardiología', 'Ginecología',
        ])->delete();
    }
};
