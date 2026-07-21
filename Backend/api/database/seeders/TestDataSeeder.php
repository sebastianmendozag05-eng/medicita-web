<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\AsistenteMedico;
use App\Models\TurnoMedico;
use App\Models\Especialidad;
use App\Models\Cita;
use App\Models\NotaConsulta;

class TestDataSeeder extends Seeder
{
    /**
     * Contraseña para TODAS las cuentas de prueba: Password123
     * php artisan db:seed --class=TestDataSeeder
     */
    public function run(): void
    {
        $pass = Hash::make('Password123');

        // ── Administrador ──
        User::firstOrCreate(
            ['email' => 'monserrat.aguilar@medicita.test'],
            ['name' => 'Monserrat Aguilar', 'password' => $pass, 'rol' => 'administrador']
        );

        // ── Recepcionistas ──
        $rec1User = User::firstOrCreate(['email' => 'valeria.reyes@medicita.test'], ['name' => 'Valeria Reyes', 'password' => $pass, 'rol' => 'recepcionista']);
        AsistenteMedico::firstOrCreate(
            ['astCorreo' => 'valeria.reyes@medicita.test'],
            ['user_id' => $rec1User->id, 'astNombre' => 'Valeria', 'astApePat' => 'Reyes', 'astApeMat' => 'Nava', 'astEdad' => 26, 'astTelefono' => '7712045781', 'astEstatus' => 1, 'astFechaReg' => now()]
        );

        $rec2User = User::firstOrCreate(['email' => 'ivan.contreras@medicita.test'], ['name' => 'Iván Contreras', 'password' => $pass, 'rol' => 'recepcionista']);
        AsistenteMedico::firstOrCreate(
            ['astCorreo' => 'ivan.contreras@medicita.test'],
            ['user_id' => $rec2User->id, 'astNombre' => 'Iván', 'astApePat' => 'Contreras', 'astApeMat' => 'Miranda', 'astEdad' => 33, 'astTelefono' => '7713398214', 'astEstatus' => 1, 'astFechaReg' => now()]
        );

        // ── Especialidades (ya sembradas por la migración) ──
        $espGeneral = Especialidad::where('espeNombre', 'Medicina General')->first();
        $espPediatria = Especialidad::where('espeNombre', 'Pediatría')->first();
        $espCardio = Especialidad::where('espeNombre', 'Cardiología')->first();

        // ── Médicos ──
        $medicosData = [
            ['nombre' => 'Adriana', 'apePat' => 'Cervantes', 'apeMat' => 'Ortiz',   'correo' => 'adriana.cervantes@medicita.test', 'cedula' => '8341027', 'sexo' => 'Femenino',  'edad' => 41, 'esp' => $espGeneral],
            ['nombre' => 'Emilio',  'apePat' => 'Domínguez', 'apeMat' => 'Salas',   'correo' => 'emilio.dominguez@medicita.test',  'cedula' => '7925610', 'sexo' => 'Masculino', 'edad' => 37, 'esp' => $espPediatria],
            ['nombre' => 'Renata',  'apePat' => 'Solórzano', 'apeMat' => 'Vega',    'correo' => 'renata.solorzano@medicita.test',  'cedula' => '9012384', 'sexo' => 'Femenino',  'edad' => 45, 'esp' => $espCardio],
        ];
        $medicos = [];
        foreach ($medicosData as $i => $d) {
            $user = User::firstOrCreate(['email' => $d['correo']], ['name' => "{$d['nombre']} {$d['apePat']}", 'password' => $pass, 'rol' => 'medico']);
            $medico = Medico::firstOrCreate(
                ['medCedula' => $d['cedula']],
                [
                    'user_id' => $user->id, 'medNombre' => $d['nombre'], 'medApePat' => $d['apePat'], 'medApeMat' => $d['apeMat'],
                    'medSexo' => $d['sexo'], 'medEdad' => $d['edad'], 'medCorreo' => $d['correo'],
                    'medTelefono' => '771405' . str_pad($i, 4, '0', STR_PAD_LEFT), 'medEstatus' => 1, 'medFechaReg' => now(),
                ]
            );
            if ($d['esp'] && $medico->especialidades()->count() === 0) {
                $medico->especialidades()->attach($d['esp']->espeId);
            }
            TurnoMedico::firstOrCreate(
                ['medId' => $medico->medId],
                ['turDiasLaborales' => 'Lunes a Viernes', 'turHoraEntrada' => '08:00:00', 'turHoraSalida' => '16:00:00']
            );
            $medicos[] = $medico;
        }

        // ── Pacientes ──
        $pacientesData = [
            ['nombre' => 'Gael',    'apePat' => 'Barrientos', 'apeMat' => 'Cruz',   'correo' => 'gael.barrientos@correo.test',  'nss' => '01019012345601', 'sexo' => 'Masculino', 'nac' => '1990-01-15', 'peso' => 78.5, 'estatura' => 1.75],
            ['nombre' => 'Ximena',  'apePat' => 'Del Toro',   'apeMat' => 'Farías', 'correo' => 'ximena.deltoro@correo.test',   'nss' => '01029512345602', 'sexo' => 'Femenino',  'nac' => '1995-06-22', 'peso' => 62.0, 'estatura' => 1.63],
            ['nombre' => 'Rodrigo', 'apePat' => 'Zamudio',    'apeMat' => null,     'correo' => 'rodrigo.zamudio@correo.test', 'nss' => '01038812345603', 'sexo' => 'Masculino', 'nac' => '1988-11-03', 'peso' => 90.2, 'estatura' => 1.80],
        ];
        $pacientes = [];
        foreach ($pacientesData as $i => $d) {
            $user = User::firstOrCreate(['email' => $d['correo']], ['name' => "{$d['nombre']} {$d['apePat']}", 'password' => $pass, 'rol' => 'paciente']);
            $paciente = Paciente::firstOrCreate(
                ['pacNSS' => $d['nss']],
                [
                    'user_id' => $user->id, 'pacNombre' => $d['nombre'], 'pacApePat' => $d['apePat'], 'pacApeMat' => $d['apeMat'],
                    'pacSexo' => $d['sexo'], 'pacFechaNac' => $d['nac'], 'pacCorreo' => $d['correo'], 'pacTelefono' => '771990' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'pacPeso' => $d['peso'], 'pacEstatura' => $d['estatura'], 'pacEstatus' => 1, 'pacFechaReg' => now(),
                ]
            );
            $pacientes[] = $paciente;
        }

        // ── Citas en distintos estados, para probar cada flujo ──
        // Agendada a futuro (Gael con Adriana) -> probar cancelar/completar
        Cita::firstOrCreate(
            ['medId' => $medicos[0]->medId, 'pacId' => $pacientes[0]->pacId, 'citFecha' => now()->addDays(2)->format('Y-m-d'), 'citHora' => '10:00:00'],
            ['citMotivo' => 'Chequeo general', 'citEstatus' => 'agendada', 'citFechaReg' => now()]
        );

        // Confirmada a futuro (Ximena con Emilio)
        Cita::firstOrCreate(
            ['medId' => $medicos[1]->medId, 'pacId' => $pacientes[1]->pacId, 'citFecha' => now()->addDays(1)->format('Y-m-d'), 'citHora' => '12:30:00'],
            ['citMotivo' => 'Consulta pediátrica de control', 'citEstatus' => 'confirmada', 'citFechaReg' => now()]
        );

        // Completada en el pasado, con su nota de consulta (Gael con Renata)
        $citaCompletada = Cita::firstOrCreate(
            ['medId' => $medicos[2]->medId, 'pacId' => $pacientes[0]->pacId, 'citFecha' => now()->subDays(5)->format('Y-m-d'), 'citHora' => '09:00:00'],
            ['citMotivo' => 'Dolor en el pecho', 'citEstatus' => 'completada', 'citFechaReg' => now()->subDays(6)]
        );
        NotaConsulta::firstOrCreate(
            ['citId' => $citaCompletada->citId],
            [
                'medId' => $medicos[2]->medId, 'pacId' => $pacientes[0]->pacId, 'notaAsistencia' => true,
                'notaDiagnostico' => 'Dolor torácico atípico, sin hallazgos isquémicos en EKG. Se sugiere seguimiento.',
                'notaReceta' => 'Ibuprofeno 400mg cada 8h por 3 días.',
            ]
        );

        // Cancelada (Rodrigo con Adriana)
        Cita::firstOrCreate(
            ['medId' => $medicos[0]->medId, 'pacId' => $pacientes[2]->pacId, 'citFecha' => now()->addDays(3)->format('Y-m-d'), 'citHora' => '15:00:00'],
            ['citMotivo' => 'Consulta general', 'citEstatus' => 'cancelada', 'citFechaReg' => now()->subDay(), 'citMotivoCancela' => 'El paciente reagendará más adelante.']
        );

        // Otra agendada a futuro (Ximena con Adriana), para probar completar + nota desde cero
        Cita::firstOrCreate(
            ['medId' => $medicos[0]->medId, 'pacId' => $pacientes[1]->pacId, 'citFecha' => now()->addDays(4)->format('Y-m-d'), 'citHora' => '11:00:00'],
            ['citMotivo' => 'Revisión de resultados de laboratorio', 'citEstatus' => 'agendada', 'citFechaReg' => now()]
        );

        $this->command->info('Datos de prueba creados. Contraseña para todas las cuentas: Password123');
        $this->command->table(['Rol', 'Correo', 'Notas'], [
            ['administrador', 'monserrat.aguilar@medicita.test', ''],
            ['recepcionista', 'valeria.reyes@medicita.test', ''],
            ['recepcionista', 'ivan.contreras@medicita.test', ''],
            ['medico', 'adriana.cervantes@medicita.test', 'Medicina General'],
            ['medico', 'emilio.dominguez@medicita.test', 'Pediatría'],
            ['medico', 'renata.solorzano@medicita.test', 'Cardiología'],
            ['paciente', 'gael.barrientos@correo.test', 'cita agendada + cita completada con nota'],
            ['paciente', 'ximena.deltoro@correo.test', 'cita confirmada + cita agendada'],
            ['paciente', 'rodrigo.zamudio@correo.test', 'cita cancelada'],
        ]);
    }
}
