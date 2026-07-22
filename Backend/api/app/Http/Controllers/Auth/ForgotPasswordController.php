<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    private const TOKEN_EXPIRA_MINUTOS = 60;

    public function enviarEnlace(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Respuesta genérica siempre, exista o no el correo (evita enumerar usuarios)
        if (!$user) {
            return response()->json(['message' => 'Si el correo existe, se envió un enlace de recuperación.']);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $url = 'http://localhost:5173/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        Mail::to($user->email)->send(new ResetPasswordMail($url));

        return response()->json(['message' => 'Si el correo existe, se envió un enlace de recuperación.']);
    }

    public function resetear(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        $registro = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$registro || !Hash::check($request->token, $registro->token)) {
            return response()->json(['message' => 'El enlace de recuperación es inválido.'], 400);
        }

        if (now()->diffInMinutes($registro->created_at) > self::TOKEN_EXPIRA_MINUTOS) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'El enlace de recuperación expiró. Solicita uno nuevo.'], 400);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => Hash::make($request->password)]);

        // Invalida todas las sesiones/tokens activos por seguridad
        $user->tokens()->delete();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }
}
