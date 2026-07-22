<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait AutorizaAccesoPaciente
{
    /**
     * Un paciente solo puede acceder a su propio recurso (por pacId).
     * Médico, recepcionista y administrador tienen acceso completo.
     */
    protected function verificarAccesoPaciente(Request $request, int $pacId): void
    {
        $user = $request->user();

        if ($user->rol === 'paciente' && (int) $user->paciente?->pacId !== $pacId) {
            abort(403, 'No tienes permiso para acceder a este recurso.');
        }
    }
}
