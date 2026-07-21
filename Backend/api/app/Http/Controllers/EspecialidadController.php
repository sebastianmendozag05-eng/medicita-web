<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;

class EspecialidadController extends Controller
{
    public function index()
    {
        return response()->json(Especialidad::orderBy('espeNombre')->get());
    }
}
