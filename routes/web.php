<?php

use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\Paciente;
use App\Http\Controllers\AntecedentesFamiliaresController;
use App\Http\Controllers\AntecedentesPersonalesController;
use App\Http\Controllers\ParametrosAntropometricosController;
use App\Http\Controllers\SignosVitalesController;

Route::get('/paciente/buscar', [PacienteController::class, 'buscarPorNombre'])->name('paciente.buscarPorNombre');
Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');

Route::get('/', function () {
    return view('welcome');
});

// Ruta para el dashboard
Route::get('/dashboard', function () {
    // Obtener el total de pacientes
    $totalPacientes = Paciente::count();

    // Obtener el paciente más reciente
    $ultimoPaciente = Paciente::latest()->first();

    // Pasar las variables a la vista
    return view('dashboard', compact('totalPacientes', 'ultimoPaciente'));
})->name('dashboard');

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas para pacientes
    Route::prefix('pacientes')->group(function () {
        Route::get('/', [PacienteController::class, 'index'])->name('paciente.index');
        Route::get('/crear', [PacienteController::class, 'create'])->name('paciente.create');
        Route::post('/', [PacienteController::class, 'store'])->name('paciente.store');
        Route::get('/{paciente}', [PacienteController::class, 'show'])->name('paciente.show');
        Route::get('/{paciente}/edit', [PacienteController::class, 'edit'])->name('paciente.edit');
        Route::put('/{paciente}', [PacienteController::class, 'update'])->name('paciente.update');
        Route::delete('/{paciente}', [PacienteController::class, 'destroy'])->name('paciente.destroy');

        // Rutas para antecedentes familiares
        Route::prefix('{paciente}/antecedentes-familiares')->group(function () {
            Route::get('/create', [AntecedentesFamiliaresController::class, 'create'])->name('antecedentes.familiares.create');
            Route::post('/', [AntecedentesFamiliaresController::class, 'store'])->name('antecedentes.familiares.store');
            Route::get('/{id}', [AntecedentesFamiliaresController::class, 'show'])->name('antecedentes.familiares.show');
            Route::get('/{id}/edit', [AntecedentesFamiliaresController::class, 'edit'])->name('antecedentes.familiares.edit');
            Route::put('/{id}', [AntecedentesFamiliaresController::class, 'update'])->name('antecedentes.familiares.update');
            Route::delete('/{id}', [AntecedentesFamiliaresController::class, 'destroy'])->name('antecedentes.familiares.destroy');
        });

        // Rutas para signos vitales
        Route::prefix('{paciente}/signosvitales')->group(function () {
            Route::get('/', [SignosVitalesController::class, 'index'])->name('signosvitales.index'); // Lista de signos vitales
            Route::get('/create', [SignosVitalesController::class, 'create'])->name('signosvitales.create'); // Formulario para crear signos vitales
            Route::post('/', [SignosVitalesController::class, 'store'])->name('signosvitales.store'); // Guardar signos vitales en la base de datos
            Route::get('/{id}', [SignosVitalesController::class, 'show'])->name('signosvitales.show'); // Mostrar detalles de un signo vital específico
            Route::get('/{id}/edit', [SignosVitalesController::class, 'edit'])->name('signosvitales.edit'); // Formulario para editar signos vitales
            Route::put('/{id}', [SignosVitalesController::class, 'update'])->name('signosvitales.update'); // Actualizar signos vitales
            Route::delete('/{id}', [SignosVitalesController::class, 'destroy'])->name('signosvitales.destroy'); // Eliminar signos vitales
        });

        // Rutas para antecedentes personales
        Route::post('{paciente}/antecedentes-personales', [AntecedentesPersonalesController::class, 'store'])->name('antecedentes.personales.store');

        // Rutas para parámetros antropométricos
        Route::post('{paciente}/parametros-antropometricos', [ParametrosAntropometricosController::class, 'store'])->name('parametros.antropometricos.store');
    });
});

// Ruta para la central de la clínica
Route::get('/central-de-clinica', function () {
    return view('central-de-clinica'); // Asegúrate de que esta vista exista
})->name('central.de.clinica');

require __DIR__.'/auth.php';
