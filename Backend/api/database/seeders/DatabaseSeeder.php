<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario admin
        DB::table('users')->insert([
            'name'       => 'Sebastian Admin',
            'email'      => 'admin@medicita.com',
            'password'   => Hash::make('12345678'),
            'rol'        => 'administrador',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Especialidad
        DB::table('especialidad')->insert([
            ['espeNombre' => 'Cardiología', 'created_at' => now(), 'updated_at' => now()],
            ['espeNombre' => 'Medicina General', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Médico
        DB::table('medico')->insert([
            'medNombre'   => 'Carlos',
            'medApePat'   => 'López',
            'medApeMat'   => 'García',
            'medSexo'     => 'Masculino',
            'medEdad'     => 45,
            'medCorreo'   => 'carlos.lopez@medicita.com',
            'medCedula'   => 'CED123456',
            'medEstatus'  => 1,
            'medFechaReg' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Turno médico
        DB::table('turno_medico')->insert([
            'medId'            => 1,
            'turDiasLaborales' => 'Lunes a Viernes',
            'turHoraEntrada'   => '08:00:00',
            'turHoraSalida'    => '16:00:00',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // Paciente
        DB::table('paciente')->insert([
            'pacNombre'   => 'Ana',
            'pacApePat'   => 'Martínez',
            'pacApeMat'   => 'Ruiz',
            'pacSexo'     => 'Femenino',
            'pacFechaNac' => '1990-05-15',
            'pacNSS'      => 'NSS987654',
            'pacCorreo'   => 'ana.martinez@email.com',
            'pacTelefono' => '555-1234',
            'pacPeso'     => 65.5,
            'pacEstatura' => 1.68,
            'pacEstatus'  => 1,
            'pacFechaReg' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Expediente
        DB::table('expediente')->insert([
            'pacId'            => 1,
            'expAlergias'      => 'Penicilina',
            'expPadecimientos' => 'Hipertensión leve',
            'expMedicamentos'  => 'Losartán 50mg',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // Cita
        DB::table('cita')->insert([
            'medId'       => 1,
            'pacId'       => 1,
            'astId'       => null,
            'citFecha'    => now()->addDay(),
            'citHora'     => '10:00:00',
            'citMotivo'   => 'Chequeo de presión arterial',
            'citEstatus'  => 'confirmada',
            'citFechaReg' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}