<?php

namespace App\Http\Controllers;

use App\Models\NotaConsulta;
use Illuminate\Http\Request;

class NotaConsultaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'citId'           => 'required|exists:cita,citId|unique:nota_consulta',
            'medId'           => 'required|exists:medico,medId',
            'pacId'           => 'required|exists:paciente,pacId',
            'notaDiagnostico' => 'required|string',
        ]);

        $nota = NotaConsulta::create([
            ...$request->only([
                'citId', 'medId', 'pacId', 'notaDiagnostico', 'notaReceta'
            ]),
            'notaAsistencia'   => true,
            'notaFechaCreacion' => now(),
        ]);

        return response()->json($nota, 201);
    }

    public function showByPaciente($pacId)
    {
        $notas = NotaConsulta::with('cita')->where('pacId', $pacId)->get();
        return response()->json($notas);
    }
}