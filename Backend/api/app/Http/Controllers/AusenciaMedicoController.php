<?php

namespace App\Http\Controllers;

use App\Models\AusenciaMedico;
use Illuminate\Http\Request;

class AusenciaMedicoController extends Controller
{
    public function index($medId)
    {
        $ausencias = AusenciaMedico::where('medId', $medId)->get();
        return response()->json($ausencias);
    }

    public function store(Request $request)
    {
        $request->validate([
            'medId'    => 'required|exists:medico,medId',
            'ausTipo'  => 'required|in:Descanso Semanal,Vacaciones,Incapacidad',
            'ausFecha' => 'required|date',
        ]);

        $ausencia = AusenciaMedico::create($request->only([
            'medId', 'ausTipo', 'ausFecha', 'ausMotivo'
        ]));

        return response()->json($ausencia, 201);
    }

    public function destroy($id)
    {
        AusenciaMedico::findOrFail($id)->delete();
        return response()->json(['message' => 'Ausencia eliminada']);
    }
}