<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Http\Controllers\Concerns\AutorizaAccesoPaciente;
use App\Http\Controllers\Concerns\RestringeAStaff;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    use AutorizaAccesoPaciente, RestringeAStaff;

    public function index(Request $request)
    {
        if ($request->user()->rol === 'paciente') {
            abort(403, 'No tienes permiso para listar pacientes.');
        }
        return response()->json(Paciente::all());
    }

    public function store(Request $request)
    {
        $this->verificarSoloStaff($request);

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

    public function show(Request $request, $id)
    {
        $this->verificarAccesoPaciente($request, (int) $id);
        $paciente = Paciente::with(['citas', 'expediente'])->findOrFail($id);
        return response()->json($paciente);
    }

    public function update(Request $request, $id)
    {
        $this->verificarAccesoPaciente($request, (int) $id);
        $paciente = Paciente::findOrFail($id);
        $data = $request->only([
            'pacNombre', 'pacApePat', 'pacApeMat', 'pacSexo',
            'pacFechaNac', 'pacNSS', 'pacCorreo', 'pacTelefono',
            'pacPeso', 'pacEstatura', 'pacEstatus'
        ]);
        if ($request->user()->rol === 'paciente') {
            unset($data['pacEstatus']);
        }
        $paciente->update($data);
        return response()->json($paciente);
    }

    public function destroy(Request $request, $id)
    {
        $this->verificarSoloStaff($request);

        $paciente = Paciente::findOrFail($id);
        $paciente->update(['pacEstatus' => 0]);
        return response()->json(['message' => 'Paciente desactivado']);
    }
}