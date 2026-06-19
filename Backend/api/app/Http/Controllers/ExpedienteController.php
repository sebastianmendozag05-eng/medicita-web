<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use Illuminate\Http\Request;

class ExpedienteController extends Controller
{
    public function show($pacId)
    {
        $expediente = Expediente::with('paciente')->where('pacId', $pacId)->firstOrFail();
        return response()->json($expediente);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pacId' => 'required|exists:paciente,pacId|unique:expediente',
        ]);

        $expediente = Expediente::create($request->only([
            'pacId', 'expAlergias', 'expPadecimientos', 'expMedicamentos'
        ]));

        return response()->json($expediente, 201);
    }

    public function update(Request $request, $pacId)
    {
        $expediente = Expediente::where('pacId', $pacId)->firstOrFail();
        $expediente->update($request->only([
            'expAlergias', 'expPadecimientos', 'expMedicamentos'
        ]));
        return response()->json($expediente);
    }
}