<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AsistenteMedico;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'rol'      => 'in:paciente,medico,recepcionista,administrador',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol'      => $request->rol ?? 'paciente',
        ]);

        // Vincular con el registro de dominio pre-creado (por el admin) que coincida en correo
        match ($user->rol) {
            'paciente'      => Paciente::where('pacCorreo', $user->email)->whereNull('user_id')->update(['user_id' => $user->id]),
            'medico'        => Medico::where('medCorreo', $user->email)->whereNull('user_id')->update(['user_id' => $user->id]),
            'recepcionista' => AsistenteMedico::where('astCorreo', $user->email)->whereNull('user_id')->update(['user_id' => $user->id]),
            default         => null,
        };

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }
}