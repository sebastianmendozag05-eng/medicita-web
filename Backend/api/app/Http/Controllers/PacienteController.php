<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    public function index()
    {
        return response()->json(Paciente::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'pacNombre'   => 'required|string|max:50',
            'pacApePat'   => 'required|string|max:50',
            'pacSexo'     => 'required|string',
            'pacFechaNac' => 'required|date',
            'pacNSS'      => 'required|string|max:20',
            'pacCorreo'   => 'required|email|max:80',
            'pacTelefono' => 'required|string|max:20',
            'pacPeso'     => 'required|numeric',
            'pacEstatura' => 'required|numeric',
        ]);

        $paciente = Paciente::create([
            ...$request->only([
                'pacNombre', 'pacApePat', 'pacApeMat', 'pacSexo',
                'pacFechaNac', 'pacNSS', 'pacCorreo', 'pacTelefono',
                'pacPeso', 'pacEstatura'
            ]),
            'pacEstatus'  => 1,
            'pacFechaReg' => now(),
        ]);

        return response()->json($paciente, 201);
    }

    public function show($id)
    {
        $paciente = Paciente::with(['citas', 'expediente'])->findOrFail($id);
        return response()->json($paciente);
    }

    public function update(Request $request, $id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->update($request->only([
            'pacNombre', 'pacApePat', 'pacApeMat', 'pacSexo',
            'pacFechaNac', 'pacNSS', 'pacCorreo', 'pacTelefono',
            'pacPeso', 'pacEstatura', 'pacEstatus'
        ]));
        return response()->json($paciente);
    }

    public function destroy($id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->update(['pacEstatus' => 0]);
        return response()->json(['message' => 'Paciente desactivado']);
    }
}