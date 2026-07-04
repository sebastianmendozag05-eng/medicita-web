<?php

namespace App\Http\Controllers;

use App\Models\AsistenteMedico;
use Illuminate\Http\Request;

class RecepcionistaController extends Controller
{
    public function index()
    {
        return response()->json(AsistenteMedico::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'recNombre'   => 'required|string|max:100',
            'recApePat'   => 'required|string|max:50',
            'recCorreo'   => 'required|email|max:80',
            'recTelefono' => 'required|string|max:20',
            'recEdad'     => 'required|integer',
        ]);

        $rec = AsistenteMedico::create([
            'astNombre'   => $request->recNombre,
            'astApePat'   => $request->recApePat,
            'astApeMat'   => $request->recApeMat ?? null,
            'astEdad'     => $request->recEdad,
            'astCorreo'   => $request->recCorreo,
            'astTelefono' => $request->recTelefono,
            'astEstatus'  => 1,
            'astFechaReg' => now(),
        ]);

        return response()->json($rec, 201);
    }

    public function show($id)
    {
        return response()->json(AsistenteMedico::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $rec = AsistenteMedico::findOrFail($id);
        $rec->update($request->only(['astNombre','astApePat','astApeMat','astEdad','astCorreo','astTelefono','astEstatus']));
        return response()->json($rec);
    }

    public function destroy($id)
    {
        $rec = AsistenteMedico::findOrFail($id);
        $rec->update(['astEstatus' => 0]);
        return response()->json(['message' => 'Recepcionista desactivada']);
    }
}