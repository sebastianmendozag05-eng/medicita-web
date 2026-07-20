<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\NotaConsultaController;
use App\Http\Controllers\TurnoMedicoController;
use App\Http\Controllers\AusenciaMedicoController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RecepcionistaController;
use App\Http\Controllers\NotificacionController;

Route::prefix('v1')->group(function () {
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/login', [LoginController::class, 'store']);
        Route::post('/forgot-password', [ForgotPasswordController::class, 'enviarEnlace']);
        Route::post('/reset-password', [ForgotPasswordController::class, 'resetear']);
    });
    Route::post('/register', [RegisterController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            $user = $request->user();
            $data = $user->toArray();
            $data['pacId'] = $user->paciente?->pacId;
            $data['medId'] = $user->medico?->medId;
            $data['astId'] = $user->asistenteMedico?->astId;
            return $data;
        });

        Route::put('/user', function (Request $request) {
            $user = $request->user();
            $request->validate([
                'name'  => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
            ]);
            $user->update($request->only(['name', 'email']));
            return $user;
        });

        Route::put('/user/password', function (Request $request) {
            $user = $request->user();
            $request->validate([
                'password_actual' => 'required|string',
                'password'        => 'required|min:8|confirmed',
            ]);
            if (!\Illuminate\Support\Facades\Hash::check($request->password_actual, $user->password)) {
                return response()->json(['message' => 'La contraseña actual es incorrecta.'], 422);
            }
            $user->update(['password' => \Illuminate\Support\Facades\Hash::make($request->password)]);
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(['message' => 'Contraseña actualizada.', 'token' => $token]);
        });

        Route::post('/logout', function (Request $request) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Sesión cerrada']);
        });

        Route::apiResource('citas', CitaController::class);
        Route::apiResource('medicos', MedicoController::class);
        Route::apiResource('pacientes', PacienteController::class);
        Route::apiResource('recepcionistas', RecepcionistaController::class);

        Route::get('/notificaciones', [NotificacionController::class, 'index']);
        Route::put('/notificaciones/leer-todas', [NotificacionController::class, 'marcarTodasLeidas']);
        Route::put('/notificaciones/{id}/leer', [NotificacionController::class, 'marcarLeida']);

        Route::post('/expediente', [ExpedienteController::class, 'store']);
        Route::get('/expediente/{pacId}', [ExpedienteController::class, 'show']);
        Route::put('/expediente/{pacId}', [ExpedienteController::class, 'update']);

        Route::post('/notas', [NotaConsultaController::class, 'store']);
        Route::get('/notas/paciente/{pacId}', [NotaConsultaController::class, 'showByPaciente']);

        Route::post('/turnos', [TurnoMedicoController::class, 'store']);
        Route::put('/turnos/{medId}', [TurnoMedicoController::class, 'update']);

        Route::get('/ausencias/{medId}', [AusenciaMedicoController::class, 'index']);
        Route::post('/ausencias', [AusenciaMedicoController::class, 'store']);
        Route::delete('/ausencias/{id}', [AusenciaMedicoController::class, 'destroy']);

        Route::get('/agenda/medico/{medId}', [AgendaController::class, 'porMedico']);
        Route::get('/agenda/medico/{medId}/semana', [AgendaController::class, 'semana']);
        Route::get('/agenda/todos', [AgendaController::class, 'todosMedicos']);

        Route::get('/reportes/resumen', [ReporteController::class, 'resumen']);
        Route::get('/reportes/periodo', [ReporteController::class, 'porPeriodo']);
        Route::get('/reportes/medico/{medId}', [ReporteController::class, 'porMedico']);
    });
});