<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function index()
    {
        return response()->json(
            Cita::with(['medico', 'paciente'])->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'medId'     => 'required|exists:medico,medId',
            'pacId'     => 'required|exists:paciente,pacId',
            'citFecha'  => 'required|date',
            'citHora'   => 'required',
            'citMotivo' => 'required|string|max:200',
        ]);

        $cita = Cita::create([
            'medId'      => $request->medId,
            'pacId'      => $request->pacId,
            'astId'      => $request->astId ?? null,
            'citFecha'   => $request->citFecha,
            'citHora'    => $request->citHora,
            'citMotivo'  => $request->citMotivo,
            'citEstatus' => 'agendada',
            'citFechaReg'=> now(),
        ]);

        return response()->json($cita, 201);
    }

    public function show($id)
    {
        $cita = Cita::with(['medico', 'paciente'])->findOrFail($id);
        return response()->json($cita);
    }

    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);

        $request->validate([
            'citEstatus' => 'in:agendada,confirmada,completada,cancelada',
        ]);

        $cita->update($request->only([
            'citFecha', 'citHora', 'citMotivo', 'citEstatus', 'citMotivoCancela'
        ]));

        return response()->json($cita);
    }

    public function destroy($id)
    {
        $cita = Cita::findOrFail($id);
        $cita->update([
            'citEstatus'      => 'cancelada',
            'citMotivoCancela'=> 'Cancelada por el sistema',
        ]);
        return response()->json(['message' => 'Cita cancelada']);
    }
}