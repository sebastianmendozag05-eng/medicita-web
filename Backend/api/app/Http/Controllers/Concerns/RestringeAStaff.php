<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait RestringeAStaff
{
    /**
     * Restringe una acción de gestión a solo recepcionista/administrador.
     */
    protected function verificarSoloStaff(Request $request): void
    {
        if (!in_array($request->user()->rol, ['recepcionista', 'administrador'])) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }
    }
}
