<?php

namespace App\Http\Controllers;

use App\Models\AntecedentesFamiliares; // Asegúrate de que el modelo AntecedenteFamiliar esté importado
use Illuminate\Http\Request;

class AntecedentesFamiliaresController extends Controller
{
    public function index() {
        $antecedentef = AntecedentesFamiliares::all(); // Obtener todos los pacientes
        //return view('pacientes.index', compact('pacientes'));  Retorna la vista con los pacientes
    }
    // Almacena antecedentes familiares
    public function store(Request $request, $pacienteId)
    {
        // Validación de los datos recibidos
        $request->validate([
            'hipertension' => 'boolean',
            'diabetes' => 'boolean',
            'cancer' => 'nullable|string|max:255',
            'obesidad' => 'boolean',
            'dislipidemia' => 'boolean',
            'otros' => 'nullable|string|max:255',
        ]);

        // Crear y asociar los antecedentes familiares al paciente
        AntecedentesFamiliares::create($request->all())([
            'paciente_id' => $pacienteId,  // Asegúrate de que 'paciente_id' sea una columna en la tabla de antecedentes familiares
            'hipertension' => $request->hipertension,
            'diabetes' => $request->diabetes,
            'cancer' => $request->cancer,
            'obesidad' => $request->obesidad,
            'dislipidemia' => $request->dislipidemia,
            'otros' => $request->otros,
        ]);

        // Redirigir con un mensaje de éxito
        return redirect()->route('paciente.index')->with('success', 'Antecedentes familiares creados con éxito.');
    }
}
