<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Http\Controllers\Concerns\RestringeAStaff;
use Illuminate\Http\Request;

class MedicoController extends Controller
{
    use RestringeAStaff;

    public function index()
    {
        return response()->json(Medico::all());
    }

    public function store(Request $request)
    {
        $this->verificarSoloStaff($request);

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
        $user = $request->user();
        if ($user->rol === 'paciente') {
            abort(403, 'No tienes permiso para editar médicos.');
        }
        if ($user->rol === 'medico' && (int) $user->medico?->medId !== (int) $id) {
            abort(403, 'No puedes editar el perfil de otro médico.');
        }

        $medico = Medico::findOrFail($id);
        $request->validate([
            'medNombre'  => 'sometimes|string|max:50',
            'medApePat'  => 'sometimes|string|max:50',
            'medApeMat'  => 'sometimes|nullable|string|max:50',
            'medSexo'    => 'sometimes|string',
            'medEdad'    => 'sometimes|integer',
            'medCorreo'  => 'sometimes|email|max:80',
            'medTelefono'=> 'sometimes|nullable|string|max:20',
            'medCedula'  => 'sometimes|string|max:20|unique:medico,medCedula,' . $id . ',medId',
        ]);
        $data = $request->only([
            'medNombre', 'medApePat', 'medApeMat', 'medSexo',
            'medEdad', 'medCorreo', 'medTelefono', 'medCedula', 'medEstatus'
        ]);
        if ($user->rol === 'medico') {
            unset($data['medEstatus']);
        }
        $medico->update($data);
        return response()->json($medico);
    }

    public function destroy(Request $request, $id)
    {
        $this->verificarSoloStaff($request);

        $medico = Medico::findOrFail($id);
        $medico->update(['medEstatus' => 0]);
        return response()->json(['message' => 'Médico desactivado']);
    }
}