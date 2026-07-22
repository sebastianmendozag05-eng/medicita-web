<?php

namespace App\Http\Controllers;

use App\Models\AusenciaMedico;
use App\Http\Controllers\Concerns\AutorizaAccesoMedico;
use Illuminate\Http\Request;

class AusenciaMedicoController extends Controller
{
    use AutorizaAccesoMedico;

    public function index(Request $request, $medId)
    {
        $this->verificarAccesoMedico($request, (int) $medId);
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

        $this->verificarAccesoMedico($request, (int) $request->medId);

        $ausencia = AusenciaMedico::create($request->only([
            'medId', 'ausTipo', 'ausFecha', 'ausMotivo'
        ]));

        return response()->json($ausencia, 201);
    }

    public function destroy(Request $request, $id)
    {
        $ausencia = AusenciaMedico::findOrFail($id);
        $this->verificarAccesoMedico($request, (int) $ausencia->medId);
        $ausencia->delete();
        return response()->json(['message' => 'Ausencia eliminada']);
    }
}
