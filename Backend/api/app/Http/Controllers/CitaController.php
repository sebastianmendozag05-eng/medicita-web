<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use App\Mail\CitaConfirmada;
use Illuminate\Support\Facades\Mail;

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

        $cita->load(['medico', 'paciente']);
        Mail::to($cita->paciente->pacCorreo)->send(new CitaConfirmada($cita));

        Notificacion::notificarA($cita->medico, 'cita', 'Nueva cita registrada',
            "El paciente {$cita->paciente->pacNombre} {$cita->paciente->pacApePat} agendó una cita para el {$cita->citFecha} a las {$cita->citHora}.");
        Notificacion::notificarA($cita->paciente, 'cita', 'Cita agendada',
            "Tu cita con el Dr. {$cita->medico->medNombre} {$cita->medico->medApePat} quedó agendada para el {$cita->citFecha} a las {$cita->citHora}.");

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

        $estatusAnterior = $cita->citEstatus;

        $cita->update($request->only([
            'citFecha', 'citHora', 'citMotivo', 'citEstatus', 'citMotivoCancela'
        ]));

        if ($request->has('citEstatus') && $request->citEstatus !== $estatusAnterior) {
            $cita->load(['medico', 'paciente']);
            $titulos = [
                'completada' => 'Cita completada',
                'cancelada'  => 'Cita cancelada',
                'confirmada' => 'Cita confirmada',
            ];
            $titulo = $titulos[$cita->citEstatus] ?? 'Cita actualizada';
            Notificacion::notificarA($cita->paciente, 'cita', $titulo,
                "Tu cita con el Dr. {$cita->medico->medNombre} {$cita->medico->medApePat} del {$cita->citFecha} cambió a estado: {$cita->citEstatus}.");
        }

        return response()->json($cita);
    }

    public function destroy($id)
    {
        $cita = Cita::with(['medico', 'paciente'])->findOrFail($id);
        $cita->update([
            'citEstatus'      => 'cancelada',
            'citMotivoCancela'=> 'Cancelada por el sistema',
        ]);

        Notificacion::notificarA($cita->paciente, 'alerta', 'Cita cancelada',
            "Tu cita con el Dr. {$cita->medico->medNombre} {$cita->medico->medApePat} del {$cita->citFecha} fue cancelada.");
        Notificacion::notificarA($cita->medico, 'alerta', 'Cita cancelada',
            "La cita con {$cita->paciente->pacNombre} {$cita->paciente->pacApePat} del {$cita->citFecha} fue cancelada.");

        return response()->json(['message' => 'Cita cancelada']);
    }
}