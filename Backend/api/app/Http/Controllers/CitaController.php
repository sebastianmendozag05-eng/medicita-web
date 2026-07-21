<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Notificacion;
use App\Http\Controllers\Concerns\ValidaDisponibilidadMedico;
use Illuminate\Http\Request;
use App\Mail\CitaConfirmada;
use App\Mail\CitaCancelada;
use Illuminate\Support\Facades\Mail;

class CitaController extends Controller
{
    use ValidaDisponibilidadMedico;

    /**
     * Verifica que el usuario autenticado tenga permiso sobre esta cita.
     * Paciente: solo sus propias citas. Médico: solo las suyas.
     * Recepcionista/administrador: todas.
     */
    private function autorizar(Request $request, Cita $cita): void
    {
        $user = $request->user();

        if ($user->rol === 'paciente' && $cita->pacId !== $user->paciente?->pacId) {
            abort(403, 'No tienes permiso sobre esta cita.');
        }

        if ($user->rol === 'medico' && $cita->medId !== $user->medico?->medId) {
            abort(403, 'No tienes permiso sobre esta cita.');
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Cita::with(['medico', 'paciente']);

        if ($user->rol === 'paciente') {
            $query->where('pacId', $user->paciente?->pacId ?? 0);
        } elseif ($user->rol === 'medico') {
            $query->where('medId', $user->medico?->medId ?? 0);
        }
        // recepcionista y administrador ven todas las citas

        return response()->json($query->get());
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

        $user = $request->user();
        if ($user->rol === 'paciente' && (int) $request->pacId !== (int) $user->paciente?->pacId) {
            abort(403, 'No puedes agendar citas a nombre de otro paciente.');
        }

        $this->validarDisponibilidad((int) $request->medId, $request->citFecha, $request->citHora);

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

    public function show(Request $request, $id)
    {
        $cita = Cita::with(['medico', 'paciente'])->findOrFail($id);
        $this->autorizar($request, $cita);
        return response()->json($cita);
    }

    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);
        $this->autorizar($request, $cita);
        $user = $request->user();

        $request->validate([
            'citEstatus' => 'in:agendada,confirmada,completada,cancelada',
        ]);

        $estatusAnterior = $cita->citEstatus;

        $data = $request->only(['citFecha', 'citHora', 'citMotivo', 'citEstatus', 'citMotivoCancela']);
        if ($user->rol === 'paciente' && isset($data['citEstatus']) && $data['citEstatus'] !== 'cancelada') {
            abort(403, 'Un paciente solo puede cancelar su cita.');
        }

        $cita->update($data);

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

            if ($cita->citEstatus === 'cancelada') {
                Mail::to($cita->paciente->pacCorreo)->send(new CitaCancelada($cita));
            }
        }

        return response()->json($cita);
    }

    public function destroy(Request $request, $id)
    {
        $cita = Cita::with(['medico', 'paciente'])->findOrFail($id);
        $this->autorizar($request, $cita);

        $cita->update([
            'citEstatus'      => 'cancelada',
            'citMotivoCancela'=> 'Cancelada por el sistema',
        ]);

        Notificacion::notificarA($cita->paciente, 'alerta', 'Cita cancelada',
            "Tu cita con el Dr. {$cita->medico->medNombre} {$cita->medico->medApePat} del {$cita->citFecha} fue cancelada.");
        Notificacion::notificarA($cita->medico, 'alerta', 'Cita cancelada',
            "La cita con {$cita->paciente->pacNombre} {$cita->paciente->pacApePat} del {$cita->citFecha} fue cancelada.");
        Mail::to($cita->paciente->pacCorreo)->send(new CitaCancelada($cita));

        return response()->json(['message' => 'Cita cancelada']);
    }
}