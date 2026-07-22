<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\AusenciaMedico;
use App\Http\Controllers\Concerns\AutorizaAccesoMedico;
use App\Http\Controllers\Concerns\ValidaDisponibilidadMedico;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    use AutorizaAccesoMedico;
    use ValidaDisponibilidadMedico;

    // Disponibilidad real de un médico en una fecha: turno laboral, huecos ya
    // ocupados por otras citas y ausencias. No expone datos de otros pacientes.
    public function disponibilidad(Request $request, $medId)
    {
        $request->validate(['fecha' => 'required|date']);

        $medico = Medico::with('turno')->findOrFail($medId);
        $turno = $medico->turno;

        if (!$turno) {
            return response()->json([
                'disponible' => false,
                'motivo' => 'El médico no tiene un turno laboral configurado.',
                'horas_disponibles' => [],
            ]);
        }

        $fecha = $request->fecha;
        $diaSemana = (int) date('w', strtotime($fecha));

        if (!in_array($diaSemana, $this->parseDiasLaborales($turno->turDiasLaborales), true)) {
            return response()->json([
                'disponible' => false,
                'motivo' => 'El médico no labora ese día de la semana.',
                'horas_disponibles' => [],
            ]);
        }

        $ausencia = AusenciaMedico::where('medId', $medId)->whereDate('ausFecha', $fecha)->first();
        if ($ausencia) {
            return response()->json([
                'disponible' => false,
                'motivo' => "El médico está ausente ese día ({$ausencia->ausTipo}).",
                'horas_disponibles' => [],
            ]);
        }

        $ocupadas = Cita::where('medId', $medId)
            ->whereDate('citFecha', $fecha)
            ->whereIn('citEstatus', ['agendada', 'confirmada'])
            ->pluck('citHora')
            ->map(fn ($h) => substr($h, 0, 5))
            ->all();

        $inicio = strtotime($turno->turHoraEntrada);
        $fin = strtotime($turno->turHoraSalida);
        $horas = [];
        for ($t = $inicio; $t < $fin; $t += 1800) {
            $hora = date('H:i', $t);
            if (!in_array($hora, $ocupadas, true)) {
                $horas[] = $hora;
            }
        }

        return response()->json([
            'disponible' => true,
            'turno' => ['inicio' => date('H:i', $inicio), 'fin' => date('H:i', $fin)],
            'horas_disponibles' => $horas,
        ]);
    }

    // Citas de un médico por fecha
    public function porMedico(Request $request, $medId)
    {
        $this->verificarAccesoMedico($request, (int) $medId);

        $request->validate([
            'fecha' => 'required|date',
        ]);

        $citas = Cita::with('paciente')
            ->where('medId', $medId)
            ->whereDate('citFecha', $request->fecha)
            ->whereIn('citEstatus', ['agendada', 'confirmada'])
            ->orderBy('citHora')
            ->get();

        return response()->json($citas);
    }

    // Agenda semanal de un médico
    public function semana(Request $request, $medId)
    {
        $this->verificarAccesoMedico($request, (int) $medId);

        $request->validate([
            'inicio' => 'required|date',
            'fin'    => 'required|date',
        ]);

        $citas = Cita::with('paciente')
            ->where('medId', $medId)
            ->whereBetween('citFecha', [$request->inicio, $request->fin])
            ->whereIn('citEstatus', ['agendada', 'confirmada'])
            ->orderBy('citFecha')
            ->orderBy('citHora')
            ->get();

        $medico = Medico::with('turno')->findOrFail($medId);

        return response()->json([
            'medico' => $medico,
            'citas'  => $citas,
        ]);
    }

    // Todos los médicos con sus citas del día (para recepcionista)
    public function todosMedicos(Request $request)
    {
        $this->verificarSoloStaff($request);

        $request->validate([
            'fecha' => 'required|date',
        ]);

        $medicos = Medico::with(['citas' => function ($q) use ($request) {
            $q->with('paciente')
              ->whereDate('citFecha', $request->fecha)
              ->whereIn('citEstatus', ['agendada', 'confirmada'])
              ->orderBy('citHora');
        }])->where('medEstatus', 1)->get();

        return response()->json($medicos);
    }
}
