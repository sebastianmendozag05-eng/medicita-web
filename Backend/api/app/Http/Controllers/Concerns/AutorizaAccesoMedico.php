<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait AutorizaAccesoMedico
{
    /**
     * Un médico solo puede acceder a su propio recurso (por medId).
     * Recepcionista y administrador tienen acceso completo.
     * Un paciente nunca debería llegar a estos endpoints.
     */
    protected function verificarAccesoMedico(Request $request, int $medId): void
    {
        $user = $request->user();

        if ($user->rol === 'paciente') {
            abort(403, 'No tienes permiso para acceder a este recurso.');
        }

        if ($user->rol === 'medico' && (int) $user->medico?->medId !== $medId) {
            abort(403, 'No tienes permiso para acceder a este recurso.');
        }
    }

    /**
     * Restringe una acción a solo recepcionista/administrador.
     */
    protected function verificarSoloStaff(Request $request): void
    {
        if (!in_array($request->user()->rol, ['recepcionista', 'administrador'])) {
            abort(403, 'No tienes permiso para acceder a este recurso.');
        }
    }
}
