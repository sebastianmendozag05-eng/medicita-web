<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Paciente;
use App\Models\Expediente;
use App\Models\Medico;
use App\Models\AsistenteMedico;
use App\Models\TurnoMedico;
use App\Models\AusenciaMedico;
use App\Models\Especialidad;
use App\Models\Cita;
use App\Models\NotaConsulta;
use App\Models\Notificacion;

class TestDataSeeder extends Seeder
{
    /**
     * Vacía las tablas de datos de prueba y siembra un set más amplio desde
     * cero: 5 recepcionistas, 8 médicos (con turnos variados y ausencias) y
     * 12 pacientes con expediente, más una veintena de citas en distintos
     * estados (con sus notas de consulta) y algunas notificaciones.
     *
     * Contraseña para TODAS las cuentas de prueba: Password123
     * php artisan db:seed --class=TestDataSeeder
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ([
            'notificacion', 'nota_consulta', 'cita', 'ausencia_medico',
            'expediente', 'medico_especialidad', 'turno_medico',
            'asistente_medico', 'paciente', 'medico',
            'personal_access_tokens', 'users',
        ] as $tabla) {
            DB::table($tabla)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $pass = Hash::make('Password123');

        // ── Administrador ──
        User::create(['name' => 'Monserrat Aguilar', 'email' => 'monserrat.aguilar@medicita.test', 'password' => $pass, 'rol' => 'administrador']);

        // ── Recepcionistas (5) ──
        $recepcionistasData = [
            ['nombre' => 'Valeria',   'apePat' => 'Reyes',     'apeMat' => 'Nava',      'correo' => 'valeria.reyes@medicita.test',    'edad' => 26],
            ['nombre' => 'Iván',      'apePat' => 'Contreras',  'apeMat' => 'Miranda',   'correo' => 'ivan.contreras@medicita.test',   'edad' => 33],
            ['nombre' => 'Fernanda',  'apePat' => 'Castillo',   'apeMat' => 'Rangel',    'correo' => 'fernanda.castillo@medicita.test','edad' => 29],
            ['nombre' => 'Gustavo',   'apePat' => 'Peña',       'apeMat' => 'Salcido',   'correo' => 'gustavo.pena@medicita.test',     'edad' => 41],
            ['nombre' => 'Daniela',   'apePat' => 'Ríos',       'apeMat' => 'Mendieta',  'correo' => 'daniela.rios@medicita.test',     'edad' => 24],
        ];
        foreach ($recepcionistasData as $i => $d) {
            $user = User::create(['name' => "{$d['nombre']} {$d['apePat']}", 'email' => $d['correo'], 'password' => $pass, 'rol' => 'recepcionista']);
            AsistenteMedico::create([
                'user_id' => $user->id, 'astNombre' => $d['nombre'], 'astApePat' => $d['apePat'], 'astApeMat' => $d['apeMat'],
                'astEdad' => $d['edad'], 'astCorreo' => $d['correo'], 'astTelefono' => '7712045' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'astEstatus' => 1, 'astFechaReg' => now(),
            ]);
        }

        // ── Especialidades (ya sembradas por la migración) ──
        $espGeneral   = Especialidad::where('espeNombre', 'Medicina General')->first();
        $espPediatria = Especialidad::where('espeNombre', 'Pediatría')->first();
        $espCardio    = Especialidad::where('espeNombre', 'Cardiología')->first();
        $espGineco    = Especialidad::where('espeNombre', 'Ginecología')->first();

        // ── Médicos (8), con turnos variados ──
        $medicosData = [
            ['nombre' => 'Adriana',  'apePat' => 'Cervantes',  'apeMat' => 'Ortiz',     'correo' => 'adriana.cervantes@medicita.test',  'cedula' => '8341027', 'sexo' => 'Femenino',  'edad' => 41, 'esp' => $espGeneral,   'dias' => 'Lunes a Viernes',            'entrada' => '08:00:00', 'salida' => '16:00:00'],
            ['nombre' => 'Emilio',   'apePat' => 'Domínguez',  'apeMat' => 'Salas',     'correo' => 'emilio.dominguez@medicita.test',   'cedula' => '7925610', 'sexo' => 'Masculino', 'edad' => 37, 'esp' => $espPediatria, 'dias' => 'Lunes a Viernes',            'entrada' => '08:00:00', 'salida' => '16:00:00'],
            ['nombre' => 'Renata',   'apePat' => 'Solórzano',  'apeMat' => 'Vega',      'correo' => 'renata.solorzano@medicita.test',   'cedula' => '9012384', 'sexo' => 'Femenino',  'edad' => 45, 'esp' => $espCardio,    'dias' => 'Lunes a Viernes',            'entrada' => '08:00:00', 'salida' => '16:00:00'],
            ['nombre' => 'Braulio',  'apePat' => 'Nájera',     'apeMat' => 'Cordero',   'correo' => 'braulio.najera@medicita.test',     'cedula' => '6689213', 'sexo' => 'Masculino', 'edad' => 39, 'esp' => $espGineco,    'dias' => 'Lunes a Viernes',            'entrada' => '09:00:00', 'salida' => '17:00:00'],
            ['nombre' => 'Fernanda', 'apePat' => 'Quintero',   'apeMat' => 'Salgado',   'correo' => 'fernanda.quintero@medicita.test',  'cedula' => '5510472', 'sexo' => 'Femenino',  'edad' => 34, 'esp' => $espGeneral,   'dias' => 'Martes a Sábado',            'entrada' => '09:00:00', 'salida' => '17:00:00'],
            ['nombre' => 'Joaquín',  'apePat' => 'Vallarta',   'apeMat' => 'Escobedo',  'correo' => 'joaquin.vallarta@medicita.test',   'cedula' => '4487215', 'sexo' => 'Masculino', 'edad' => 50, 'esp' => $espPediatria, 'dias' => 'Lunes, Miércoles y Viernes', 'entrada' => '08:00:00', 'salida' => '14:00:00'],
            ['nombre' => 'Itzel',    'apePat' => 'Marroquín',  'apeMat' => 'Beltrán',   'correo' => 'itzel.marroquin@medicita.test',    'cedula' => '3321987', 'sexo' => 'Femenino',  'edad' => 36, 'esp' => $espCardio,    'dias' => 'Lunes a Viernes',            'entrada' => '10:00:00', 'salida' => '18:00:00'],
            ['nombre' => 'Rodrigo',  'apePat' => 'Aceves',     'apeMat' => 'Palomino',  'correo' => 'rodrigo.aceves@medicita.test',     'cedula' => '2287654', 'sexo' => 'Masculino', 'edad' => 48, 'esp' => $espGineco,    'dias' => 'Lunes a Viernes',            'entrada' => '08:00:00', 'salida' => '16:00:00'],
        ];
        $medicos = [];
        foreach ($medicosData as $i => $d) {
            $user = User::create(['name' => "{$d['nombre']} {$d['apePat']}", 'email' => $d['correo'], 'password' => $pass, 'rol' => 'medico']);
            $medico = Medico::create([
                'user_id' => $user->id, 'medNombre' => $d['nombre'], 'medApePat' => $d['apePat'], 'medApeMat' => $d['apeMat'],
                'medSexo' => $d['sexo'], 'medEdad' => $d['edad'], 'medCorreo' => $d['correo'], 'medCedula' => $d['cedula'],
                'medTelefono' => '771405' . str_pad($i, 4, '0', STR_PAD_LEFT), 'medEstatus' => 1, 'medFechaReg' => now(),
            ]);
            if ($d['esp']) {
                $medico->especialidades()->attach($d['esp']->espeId);
            }
            TurnoMedico::create([
                'medId' => $medico->medId, 'turDiasLaborales' => $d['dias'],
                'turHoraEntrada' => $d['entrada'], 'turHoraSalida' => $d['salida'],
            ]);
            $medicos[] = $medico;
        }

        // ── Ausencias, para probar el bloqueo de disponibilidad ──
        AusenciaMedico::create(['medId' => $medicos[0]->medId, 'ausTipo' => 'Incapacidad',      'ausFecha' => now()->addDays(3)->format('Y-m-d'), 'ausMotivo' => 'Reposo médico por gripe.']);
        AusenciaMedico::create(['medId' => $medicos[7]->medId, 'ausTipo' => 'Vacaciones',        'ausFecha' => now()->addDays(6)->format('Y-m-d'), 'ausMotivo' => 'Periodo vacacional programado.']);
        AusenciaMedico::create(['medId' => $medicos[7]->medId, 'ausTipo' => 'Vacaciones',        'ausFecha' => now()->addDays(7)->format('Y-m-d'), 'ausMotivo' => 'Periodo vacacional programado.']);
        AusenciaMedico::create(['medId' => $medicos[5]->medId, 'ausTipo' => 'Descanso Semanal',  'ausFecha' => now()->addDays(2)->format('Y-m-d'), 'ausMotivo' => 'Día no laboral del turno.']);

        // ── Pacientes (12), cada uno con expediente ──
        $pacientesData = [
            ['nombre' => 'Gael',      'apePat' => 'Barrientos',  'apeMat' => 'Cruz',     'correo' => 'gael.barrientos@correo.test',   'nss' => '01019012345601', 'sexo' => 'Masculino', 'nac' => '1990-01-15', 'peso' => 78.5, 'estatura' => 1.75, 'alergias' => 'Ninguna conocida',        'padecimientos' => 'Ninguno',                 'medicamentos' => 'Ninguno'],
            ['nombre' => 'Ximena',    'apePat' => 'Del Toro',    'apeMat' => 'Farías',   'correo' => 'ximena.deltoro@correo.test',    'nss' => '01029512345602', 'sexo' => 'Femenino',  'nac' => '1995-06-22', 'peso' => 62.0, 'estatura' => 1.63, 'alergias' => 'Penicilina',              'padecimientos' => 'Asma leve',               'medicamentos' => 'Salbutamol inhalado'],
            ['nombre' => 'Rodrigo',   'apePat' => 'Zamudio',     'apeMat' => null,       'correo' => 'rodrigo.zamudio@correo.test',   'nss' => '01038812345603', 'sexo' => 'Masculino', 'nac' => '1988-11-03', 'peso' => 90.2, 'estatura' => 1.80, 'alergias' => 'Ninguna conocida',        'padecimientos' => 'Hipertensión controlada', 'medicamentos' => 'Losartán 50mg'],
            ['nombre' => 'Camila',    'apePat' => 'Osorio',      'apeMat' => 'Beltrán',  'correo' => 'camila.osorio@correo.test',     'nss' => '01049312345604', 'sexo' => 'Femenino',  'nac' => '1993-03-30', 'peso' => 58.4, 'estatura' => 1.60, 'alergias' => 'Mariscos',                'padecimientos' => 'Ninguno',                 'medicamentos' => 'Ninguno'],
            ['nombre' => 'Diego',     'apePat' => 'Villaseñor',  'apeMat' => 'Nájera',   'correo' => 'diego.villasenor@correo.test',  'nss' => '01059612345605', 'sexo' => 'Masculino', 'nac' => '1996-09-10', 'peso' => 82.1, 'estatura' => 1.78, 'alergias' => 'Ninguna conocida',        'padecimientos' => 'Migraña episódica',       'medicamentos' => 'Naproxeno según necesidad'],
            ['nombre' => 'Fernanda',  'apePat' => 'Landeros',    'apeMat' => 'Quiroz',   'correo' => 'fernanda.landeros@correo.test', 'nss' => '01068712345606', 'sexo' => 'Femenino',  'nac' => '1987-12-05', 'peso' => 67.3, 'estatura' => 1.65, 'alergias' => 'Sulfas',                  'padecimientos' => 'Hipotiroidismo',          'medicamentos' => 'Levotiroxina 50mcg'],
            ['nombre' => 'Mauricio',  'apePat' => 'Corcuera',    'apeMat' => 'Peña',     'correo' => 'mauricio.corcuera@correo.test', 'nss' => '01079212345607', 'sexo' => 'Masculino', 'nac' => '1992-04-18', 'peso' => 95.0, 'estatura' => 1.82, 'alergias' => 'Ninguna conocida',        'padecimientos' => 'Diabetes tipo 2',         'medicamentos' => 'Metformina 850mg'],
            ['nombre' => 'Valentina', 'apePat' => 'Espinosa',    'apeMat' => 'Robledo',  'correo' => 'valentina.espinosa@correo.test','nss' => '01089912345608', 'sexo' => 'Femenino',  'nac' => '1999-07-27', 'peso' => 55.6, 'estatura' => 1.58, 'alergias' => 'Ninguna conocida',        'padecimientos' => 'Ninguno',                 'medicamentos' => 'Ninguno'],
            ['nombre' => 'Santiago',  'apePat' => 'Bermúdez',    'apeMat' => 'Alcalá',   'correo' => 'santiago.bermudez@correo.test', 'nss' => '01098412345609', 'sexo' => 'Masculino', 'nac' => '1984-02-14', 'peso' => 88.7, 'estatura' => 1.76, 'alergias' => 'Aspirina',                'padecimientos' => 'Gastritis crónica',       'medicamentos' => 'Omeprazol 20mg'],
            ['nombre' => 'Paola',     'apePat' => 'Manríquez',   'apeMat' => 'Solís',    'correo' => 'paola.manriquez@correo.test',   'nss' => '01109712345610', 'sexo' => 'Femenino',  'nac' => '1997-10-08', 'peso' => 60.2, 'estatura' => 1.62, 'alergias' => 'Ninguna conocida',        'padecimientos' => 'Ninguno',                 'medicamentos' => 'Ninguno'],
            ['nombre' => 'Iker',      'apePat' => 'Talavera',    'apeMat' => 'Duarte',   'correo' => 'iker.talavera@correo.test',     'nss' => '01119112345611', 'sexo' => 'Masculino', 'nac' => '1991-05-25', 'peso' => 79.8, 'estatura' => 1.74, 'alergias' => 'Ninguna conocida',        'padecimientos' => 'Ninguno',                 'medicamentos' => 'Ninguno'],
            ['nombre' => 'Regina',    'apePat' => 'Cifuentes',   'apeMat' => 'Bravo',    'correo' => 'regina.cifuentes@correo.test',  'nss' => '01129412345612', 'sexo' => 'Femenino',  'nac' => '1994-08-19', 'peso' => 64.5, 'estatura' => 1.66, 'alergias' => 'Polen',                   'padecimientos' => 'Rinitis alérgica',        'medicamentos' => 'Loratadina 10mg'],
        ];
        $pacientes = [];
        foreach ($pacientesData as $i => $d) {
            $user = User::create(['name' => "{$d['nombre']} {$d['apePat']}", 'email' => $d['correo'], 'password' => $pass, 'rol' => 'paciente']);
            $paciente = Paciente::create([
                'user_id' => $user->id, 'pacNombre' => $d['nombre'], 'pacApePat' => $d['apePat'], 'pacApeMat' => $d['apeMat'],
                'pacSexo' => $d['sexo'], 'pacFechaNac' => $d['nac'], 'pacNSS' => $d['nss'], 'pacCorreo' => $d['correo'],
                'pacTelefono' => '771990' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'pacPeso' => $d['peso'], 'pacEstatura' => $d['estatura'], 'pacEstatus' => 1, 'pacFechaReg' => now(),
            ]);
            Expediente::create([
                'pacId' => $paciente->pacId, 'expAlergias' => $d['alergias'],
                'expPadecimientos' => $d['padecimientos'], 'expMedicamentos' => $d['medicamentos'],
            ]);
            $pacientes[] = $paciente;
        }

        // ── Citas: varias por médico, en distintos estados ──
        // [medico_idx, paciente_idx, offset_dias, hora, estatus, motivo, diagnostico?, receta?]
        $citasData = [
            [0, 0,  2, '10:00:00', 'agendada',   'Chequeo general'],
            [0, 1,  4, '11:00:00', 'agendada',   'Revisión de resultados de laboratorio'],
            [0, 2,  3, '15:00:00', 'cancelada',  'Consulta general', null, null, 'El paciente reagendará más adelante.'],
            [0, 3, -5, '09:00:00', 'completada', 'Dolor abdominal',   'Gastritis leve, se recomienda dieta blanda y seguimiento en 2 semanas.', 'Omeprazol 20mg cada 24h por 10 días.'],

            [1, 4,  1, '12:30:00', 'confirmada', 'Consulta pediátrica de control'],
            [1, 5,  2, '13:00:00', 'agendada',   'Vacunación de refuerzo'],
            [1, 6, -3, '09:30:00', 'completada', 'Fiebre y tos persistente', 'Infección respiratoria alta viral, sin datos de alarma.', 'Paracetamol 500mg cada 8h por 3 días.'],

            [2, 0, -5, '09:00:00', 'completada', 'Dolor en el pecho', 'Dolor torácico atípico, sin hallazgos isquémicos en EKG. Se sugiere seguimiento.', 'Ibuprofeno 400mg cada 8h por 3 días.'],
            [2, 7,  5, '10:30:00', 'agendada',   'Palpitaciones ocasionales'],
            [2, 8,  1, '14:00:00', 'confirmada', 'Control post-infarto'],

            [3, 9,  6, '09:30:00', 'agendada',   'Consulta ginecológica de rutina'],
            [3, 10, 3, '11:00:00', 'confirmada', 'Revisión de resultados de ultrasonido'],
            [3, 1, -2, '10:00:00', 'completada', 'Dolor pélvico', 'Quiste ovárico funcional, se indica control ecográfico en 6 semanas.', 'Naproxeno 250mg cada 12h si hay dolor.'],

            [4, 11, 2, '09:30:00', 'agendada',   'Consulta general'],
            [4, 2,  8, '10:30:00', 'agendada',   'Chequeo anual'],
            [4, 3,  4, '13:00:00', 'cancelada',  'Consulta general', null, null, 'Cambio de horario del paciente.'],

            [5, 4,  1, '08:30:00', 'confirmada', 'Consulta pediátrica'],
            [5, 6,  8, '09:30:00', 'agendada',   'Revisión de crecimiento y desarrollo'],

            [6, 7, -1, '10:30:00', 'completada', 'Arritmia leve', 'Extrasístoles ventriculares aisladas, benignas. Se solicita Holter de control.', 'Sin tratamiento farmacológico por el momento.'],
            [6, 8,  3, '11:30:00', 'agendada',   'Dolor torácico atípico'],
            [6, 9,  5, '16:00:00', 'confirmada', 'Control de hipertensión'],

            [7, 10, 4, '09:00:00', 'agendada',   'Consulta ginecológica'],
            [7, 11, 1, '10:00:00', 'confirmada', 'Resultados de papanicolau'],
        ];

        foreach ($citasData as $c) {
            [$medIdx, $pacIdx, $offset, $hora, $estatus, $motivo] = $c;
            $diagnostico = $c[6] ?? null;
            $receta      = $c[7] ?? null;
            $motivoCancela = $c[8] ?? null;

            $fecha = $offset >= 0 ? now()->addDays($offset) : now()->subDays(abs($offset));

            $cita = Cita::create([
                'medId' => $medicos[$medIdx]->medId, 'pacId' => $pacientes[$pacIdx]->pacId,
                'citFecha' => $fecha->format('Y-m-d'), 'citHora' => $hora, 'citMotivo' => $motivo,
                'citEstatus' => $estatus, 'citFechaReg' => $offset >= 0 ? now() : $fecha->copy()->subDay(),
                'citMotivoCancela' => $motivoCancela,
            ]);

            if ($estatus === 'completada' && $diagnostico) {
                NotaConsulta::create([
                    'citId' => $cita->citId, 'medId' => $medicos[$medIdx]->medId, 'pacId' => $pacientes[$pacIdx]->pacId,
                    'notaAsistencia' => true, 'notaDiagnostico' => $diagnostico, 'notaReceta' => $receta,
                ]);
            }

            $cita->load(['medico', 'paciente']);
            if ($estatus === 'agendada' || $estatus === 'confirmada') {
                Notificacion::notificarA($cita->medico, 'cita', 'Nueva cita registrada',
                    "El paciente {$cita->paciente->pacNombre} {$cita->paciente->pacApePat} agendó una cita para el {$cita->citFecha} a las {$cita->citHora}.");
                Notificacion::notificarA($cita->paciente, 'cita', 'Cita agendada',
                    "Tu cita con el Dr. {$cita->medico->medNombre} {$cita->medico->medApePat} quedó agendada para el {$cita->citFecha} a las {$cita->citHora}.");
            } elseif ($estatus === 'cancelada') {
                Notificacion::notificarA($cita->paciente, 'alerta', 'Cita cancelada',
                    "Tu cita con el Dr. {$cita->medico->medNombre} {$cita->medico->medApePat} del {$cita->citFecha} fue cancelada.");
            } elseif ($estatus === 'completada') {
                Notificacion::notificarA($cita->paciente, 'cita', 'Cita completada',
                    "Tu cita con el Dr. {$cita->medico->medNombre} {$cita->medico->medApePat} del {$cita->citFecha} fue marcada como completada.");
            }
        }

        $this->command->info('Base de datos vaciada y repoblada. Contraseña para todas las cuentas: Password123');
        $this->command->table(['Rol', 'Cantidad'], [
            ['administrador', 1],
            ['recepcionista', count($recepcionistasData)],
            ['medico', count($medicosData)],
            ['paciente', count($pacientesData)],
            ['citas creadas', count($citasData)],
        ]);
    }
}
