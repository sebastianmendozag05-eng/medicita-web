<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $userData = $user->toArray();
        $userData['pacId'] = $user->paciente?->pacId;
        $userData['medId'] = $user->medico?->medId;
        $userData['astId'] = $user->asistenteMedico?->astId;

        return response()->json(['user' => $userData, 'token' => $token]);
    }
}