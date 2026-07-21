<?php

namespace App\Http\Controllers\Concerns;

use App\Models\AusenciaMedico;
use App\Models\Cita;
use App\Models\Medico;

trait ValidaDisponibilidadMedico
{
    // "Lunes a Viernes" / "Lunes, Miércoles y Viernes" -> [1,2,3,4,5] (0=domingo)
    protected function parseDiasLaborales(string $str): array
    {
        $mapa = [
            'domingo' => 0, 'lunes' => 1, 'martes' => 2,
            'miercoles' => 3, 'miércoles' => 3, 'jueves' => 4,
            'viernes' => 5, 'sabado' => 6, 'sábado' => 6,
        ];
        $str = mb_strtolower(trim($str));

        if (str_contains($str, ' a ')) {
            [$ini, $fin] = array_map('trim', explode(' a ', $str, 2));
            if (!isset($mapa[$ini]) || !isset($mapa[$fin])) return [];
            $dias = [];
            $d = $mapa[$ini];
            while (true) {
                $dias[] = $d;
                if ($d === $mapa[$fin]) break;
                $d = ($d + 1) % 7;
            }
            return $dias;
        }

        $dias = [];
        foreach (preg_split('/,|\by\b/', $str) as $parte) {
            $parte = trim($parte);
            if (isset($mapa[$parte])) $dias[] = $mapa[$parte];
        }
        return $dias;
    }

    /**
     * Valida que $medId pueda atender el $fecha/$hora dados. Aborta con 422
     * si el médico no labora ese día, está ausente, o ya tiene otra cita a
     * esa hora (excluyendo $excluirCitId, útil al actualizar una cita).
     */
    protected function validarDisponibilidad(int $medId, string $fecha, string $hora, ?int $excluirCitId = null): void
    {
        $medico = Medico::with('turno')->findOrFail($medId);
        $turno = $medico->turno;

        if (!$turno) {
            abort(422, 'El médico no tiene un turno laboral configurado.');
        }

        $diaSemana = (int) date('w', strtotime($fecha));
        if (!in_array($diaSemana, $this->parseDiasLaborales($turno->turDiasLaborales), true)) {
            abort(422, 'El médico no labora ese día de la semana.');
        }

        $horaComparar = substr($hora, 0, 5);
        if ($horaComparar < substr($turno->turHoraEntrada, 0, 5) || $horaComparar >= substr($turno->turHoraSalida, 0, 5)) {
            abort(422, 'La hora seleccionada está fuera del horario laboral del médico.');
        }

        $ausencia = AusenciaMedico::where('medId', $medId)->whereDate('ausFecha', $fecha)->first();
        if ($ausencia) {
            abort(422, "El médico está ausente ese día ({$ausencia->ausTipo}).");
        }

        $existe = Cita::where('medId', $medId)
            ->whereDate('citFecha', $fecha)
            ->where('citHora', $hora)
            ->whereIn('citEstatus', ['agendada', 'confirmada'])
            ->when($excluirCitId, fn ($q) => $q->where('citId', '!=', $excluirCitId))
            ->exists();

        if ($existe) {
            abort(422, 'El médico ya tiene otra cita agendada en ese horario.');
        }
    }
}
