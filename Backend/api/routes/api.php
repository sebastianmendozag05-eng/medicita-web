<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
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

Route::prefix('v1')->group(function () {
    Route::post('/register', [RegisterController::class, 'store']);
    Route::post('/login', [LoginController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            $user = $request->user();
            $data = $user->toArray();
            $data['pacId'] = $user->paciente?->pacId;
            $data['medId'] = $user->medico?->medId;
            $data['astId'] = $user->asistenteMedico?->astId;
            return $data;
        });

        Route::post('/logout', function (Request $request) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Sesión cerrada']);
        });

        Route::apiResource('citas', CitaController::class);
        Route::apiResource('medicos', MedicoController::class);
        Route::apiResource('pacientes', PacienteController::class);
        Route::apiResource('recepcionistas', RecepcionistaController::class);

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