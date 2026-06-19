<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    // Citas por período
    public function porPeriodo(Request $request)
    {
        $request->validate([
            'inicio' => 'required|date',
            'fin'    => 'required|date',
        ]);

        $citas = Cita::with(['medico', 'paciente'])
            ->whereBetween('citFecha', [$request->inicio, $request->fin])
            ->orderBy('citFecha')
            ->get();

        return response()->json([
            'total'  => $citas->count(),
            'citas'  => $citas,
        ]);
    }

    // Citas por médico
    public function porMedico(Request $request, $medId)
    {
        $citas = Cita::with('paciente')
            ->where('medId', $medId)
            ->orderBy('citFecha', 'desc')
            ->get();

        $medico = Medico::findOrFail($medId);

        return response()->json([
            'medico'      => $medico,
            'total'       => $citas->count(),
            'completadas' => $citas->where('citEstatus', 'completada')->count(),
            'canceladas'  => $citas->where('citEstatus', 'cancelada')->count(),
            'pendientes'  => $citas->whereIn('citEstatus', ['agendada', 'confirmada'])->count(),
            'citas'       => $citas,
        ]);
    }

    // Resumen general
    public function resumen()
    {
        $total       = Cita::count();
        $completadas = Cita::where('citEstatus', 'completada')->count();
        $canceladas  = Cita::where('citEstatus', 'cancelada')->count();
        $pendientes  = Cita::whereIn('citEstatus', ['agendada', 'confirmada'])->count();
        $medicos     = Medico::where('medEstatus', 1)->count();

        return response()->json([
            'total_citas'       => $total,
            'completadas'       => $completadas,
            'canceladas'        => $canceladas,
            'pendientes'        => $pendientes,
            'medicos_activos'   => $medicos,
        ]);
    }
}