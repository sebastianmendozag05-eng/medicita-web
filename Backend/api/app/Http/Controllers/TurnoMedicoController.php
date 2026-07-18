<?php

namespace App\Http\Controllers;

use App\Models\TurnoMedico;
use App\Http\Controllers\Concerns\AutorizaAccesoMedico;
use Illuminate\Http\Request;

class TurnoMedicoController extends Controller
{
    use AutorizaAccesoMedico;

    public function store(Request $request)
    {
        $request->validate([
            'medId'            => 'required|exists:medico,medId|unique:turno_medico',
            'turDiasLaborales' => 'required|string',
            'turHoraEntrada'   => 'required',
            'turHoraSalida'    => 'required',
        ]);

        $this->verificarAccesoMedico($request, (int) $request->medId);

        $turno = TurnoMedico::create($request->only([
            'medId', 'turDiasLaborales', 'turHoraEntrada', 'turHoraSalida'
        ]));

        return response()->json($turno, 201);
    }

    public function update(Request $request, $medId)
    {
        $this->verificarAccesoMedico($request, (int) $medId);
        $turno = TurnoMedico::where('medId', $medId)->firstOrFail();
        $turno->update($request->only([
            'turDiasLaborales', 'turHoraEntrada', 'turHoraSalida'
        ]));
        return response()->json($turno);
    }
}