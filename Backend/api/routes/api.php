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

Route::post('/register', [RegisterController::class, 'store']);
Route::post('/login', [LoginController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    });

    Route::apiResource('citas', CitaController::class);
    Route::apiResource('medicos', MedicoController::class);
    Route::apiResource('pacientes', PacienteController::class);

    // Expediente
    Route::post('/expediente', [ExpedienteController::class, 'store']);
    Route::get('/expediente/{pacId}', [ExpedienteController::class, 'show']);
    Route::put('/expediente/{pacId}', [ExpedienteController::class, 'update']);

    // Notas de consulta
    Route::post('/notas', [NotaConsultaController::class, 'store']);
    Route::get('/notas/paciente/{pacId}', [NotaConsultaController::class, 'showByPaciente']);

    // Turnos
    Route::post('/turnos', [TurnoMedicoController::class, 'store']);
    Route::put('/turnos/{medId}', [TurnoMedicoController::class, 'update']);

    // Ausencias
    Route::get('/ausencias/{medId}', [AusenciaMedicoController::class, 'index']);
    Route::post('/ausencias', [AusenciaMedicoController::class, 'store']);
    Route::delete('/ausencias/{id}', [AusenciaMedicoController::class, 'destroy']);
    // Agenda
    Route::get('/agenda/medico/{medId}', [AgendaController::class, 'porMedico']);
    Route::get('/agenda/medico/{medId}/semana', [AgendaController::class, 'semana']);
    Route::get('/agenda/todos', [AgendaController::class, 'todosMedicos']);
    // Reportes
    Route::get('/reportes/resumen', [ReporteController::class, 'resumen']);
    Route::get('/reportes/periodo', [ReporteController::class, 'porPeriodo']);
    Route::get('/reportes/medico/{medId}', [ReporteController::class, 'porMedico']);
});