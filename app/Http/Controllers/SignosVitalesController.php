<?php

namespace App\Http\Controllers;

use App\Models\SignosVitales;
use App\Models\Paciente;
use Illuminate\Http\Request;

class SignosVitalesController extends Controller
{
    // Muestra la lista de signos vitales
    public function index() {
        $signosVitales = SignosVitales::with('paciente')->get(); // Obtener todos los signos vitales con sus pacientes
        return view('signosvitales.index', compact('signosVitales')); // Retorna la vista con los signos vitales
    }

    // Muestra el formulario para crear nuevos signos vitales
    public function create() {
        $pacientes = Paciente::all(); // Obtener todos los pacientes para el select
        return view('signosvitales.create', compact('pacientes')); // Retorna la vista para crear signos vitales
    }

    // Almacena un nuevo registro de signos vitales
    public function store(Request $request) {
        // Validación de los datos recibidos
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'presion_arterial' => 'required|numeric|between:0,999.99',
            'frecuencia_cardiaca' => 'required|integer',
            'frecuencia_respiratoria' => 'required|integer',
            'temperatura' => 'required|numeric|between:0,999.99',
            'saturacion_oxigeno' => 'required|numeric|between:0,999.99',
        ]);

        // Crear el registro de signos vitales
        SignosVitales::create($request->all());

        // Redirigir a la lista de signos vitales con un mensaje de éxito
        return redirect()->route('signosvitales.index')->with('success', 'Signos vitales creados con éxito.');
    }

    // Muestra los detalles de un registro específico de signos vitales
    public function show(SignosVitales $signoVital) {
        return view('signosvitales.show', compact('signoVital')); // Retorna la vista con los detalles del registro
    }

    // Muestra el formulario para editar un registro existente de signos vitales
    public function edit(SignosVitales $signoVital) {
        $pacientes = Paciente::all(); // Obtener todos los pacientes para poder editar el paciente relacionado
        return view('signosvitales.edit', compact('signoVital', 'pacientes')); // Retorna la vista para editar signos vitales
    }

    // Actualiza un registro existente de signos vitales
    public function update(Request $request, SignosVitales $signoVital) {
        // Validación de los datos recibidos
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'presion_arterial' => 'required|numeric|between:0,999.99',
            'frecuencia_cardiaca' => 'required|integer',
            'frecuencia_respiratoria' => 'required|integer',
            'temperatura' => 'required|numeric|between:0,999.99',
            'saturacion_oxigeno' => 'required|numeric|between:0,999.99',
        ]);

        // Actualizar el registro de signos vitales
        $signoVital->update($request->all());

        // Redirigir a la lista de signos vitales con un mensaje de éxito
        return redirect()->route('signosvitales.index')->with('success', 'Signos vitales actualizados con éxito.');
    }

    // Elimina un registro existente de signos vitales
    public function destroy(SignosVitales $signoVital) {
        $signoVital->delete(); // Elimina el registro

        // Redirigir a la lista de signos vitales con un mensaje de éxito
        return redirect()->route('signosvitales.index')->with('success', 'Signos vitales eliminados con éxito.');
    }
}
