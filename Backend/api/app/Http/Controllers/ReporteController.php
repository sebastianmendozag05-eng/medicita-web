<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Http\Controllers\Concerns\AutorizaAccesoMedico;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    use AutorizaAccesoMedico;

    // Citas por período (reporte general: solo staff)
    public function porPeriodo(Request $request)
    {
        $this->verificarSoloStaff($request);

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

    // Citas por médico (el propio médico o staff)
    public function porMedico(Request $request, $medId)
    {
        $this->verificarAccesoMedico($request, (int) $medId);

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

    // Resumen general (solo staff)
    public function resumen(Request $request)
    {
        $this->verificarSoloStaff($request);

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
