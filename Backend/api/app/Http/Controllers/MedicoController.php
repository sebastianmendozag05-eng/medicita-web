<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use Illuminate\Http\Request;

class MedicoController extends Controller
{
    public function index()
    {
        return response()->json(Medico::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'medNombre'  => 'required|string|max:50',
            'medApePat'  => 'required|string|max:50',
            'medSexo'    => 'required|string',
            'medEdad'    => 'required|integer',
            'medCorreo'  => 'required|email|max:80',
            'medCedula'  => 'required|string|max:20|unique:medico',
        ]);

        $medico = Medico::create([
            ...$request->only([
                'medNombre', 'medApePat', 'medApeMat', 'medSexo',
                'medEdad', 'medCorreo', 'medCedula'
            ]),
            'medEstatus'  => 1,
            'medFechaReg' => now(),
        ]);

        return response()->json($medico, 201);
    }

    public function show($id)
    {
        $medico = Medico::with('turno')->findOrFail($id);
        return response()->json($medico);
    }

    public function update(Request $request, $id)
    {
        $medico = Medico::findOrFail($id);
        $medico->update($request->only([
            'medNombre', 'medApePat', 'medApeMat', 'medSexo',
            'medEdad', 'medCorreo', 'medCedula', 'medEstatus'
        ]));
        return response()->json($medico);
    }

    public function destroy($id)
    {
        $medico = Medico::findOrFail($id);
        $medico->update(['medEstatus' => 0]);
        return response()->json(['message' => 'Médico desactivado']);
    }
}