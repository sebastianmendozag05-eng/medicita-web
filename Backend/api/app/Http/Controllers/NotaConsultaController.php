<?php

namespace App\Http\Controllers;

use App\Models\NotaConsulta;
use App\Http\Controllers\Concerns\AutorizaAccesoPaciente;
use Illuminate\Http\Request;

class NotaConsultaController extends Controller
{
    use AutorizaAccesoPaciente;

    public function store(Request $request)
    {
        $request->validate([
            'citId'           => 'required|exists:cita,citId|unique:nota_consulta',
            'medId'           => 'required|exists:medico,medId',
            'pacId'           => 'required|exists:paciente,pacId',
            'notaDiagnostico' => 'required|string',
        ]);

        $user = $request->user();
        if ($user->rol === 'medico' && (int) $user->medico?->medId !== (int) $request->medId) {
            abort(403, 'No puedes registrar notas de consulta a nombre de otro médico.');
        }

        $nota = NotaConsulta::create([
            ...$request->only([
                'citId', 'medId', 'pacId', 'notaDiagnostico', 'notaReceta'
            ]),
            'notaAsistencia'   => true,
            'notaFechaCreacion' => now(),
        ]);

        return response()->json($nota, 201);
    }

    public function showByPaciente(Request $request, $pacId)
    {
        $this->verificarAccesoPaciente($request, (int) $pacId);
        $notas = NotaConsulta::with('cita')->where('pacId', $pacId)->get();
        return response()->json($notas);
    }
}