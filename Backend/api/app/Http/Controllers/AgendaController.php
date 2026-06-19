<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    // Citas de un médico por fecha
    public function porMedico(Request $request, $medId)
    {
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