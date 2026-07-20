<?php

namespace App\Http\Controllers;

use App\Models\AsistenteMedico;
use App\Http\Controllers\Concerns\RestringeAStaff;
use Illuminate\Http\Request;

class RecepcionistaController extends Controller
{
    use RestringeAStaff;

    public function index(Request $request)
    {
        $this->verificarSoloStaff($request);
        return response()->json(AsistenteMedico::all());
    }

    public function store(Request $request)
    {
        $this->verificarSoloStaff($request);

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

    public function show(Request $request, $id)
    {
        $this->verificarSoloStaff($request);
        return response()->json(AsistenteMedico::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $this->verificarSoloStaff($request);

        $rec = AsistenteMedico::findOrFail($id);
        $data = [
            'astNombre'   => $request->recNombre   ?? $rec->astNombre,
            'astApePat'   => $request->recApePat   ?? $rec->astApePat,
            'astApeMat'   => $request->recApeMat   ?? $rec->astApeMat,
            'astEdad'     => $request->recEdad     ?? $rec->astEdad,
            'astCorreo'   => $request->recCorreo   ?? $rec->astCorreo,
            'astTelefono' => $request->recTelefono ?? $rec->astTelefono,
            'astEstatus'  => $request->astEstatus  ?? $rec->astEstatus,
        ];
        if ((int) $request->user()->asistenteMedico?->astId === (int) $rec->astId) {
            $data['astEstatus'] = $rec->astEstatus;
        }
        $rec->update($data);
        return response()->json($rec);
    }

    public function destroy(Request $request, $id)
    {
        $this->verificarSoloStaff($request);

        $rec = AsistenteMedico::findOrFail($id);
        $rec->update(['astEstatus' => 0]);
        return response()->json(['message' => 'Recepcionista desactivada']);
    }
}
