<?php

namespace App\Http\Controllers;

use App\Models\AntecedentePersonal; // Asegúrate de que el modelo AntecedentePersonal esté importado
use Illuminate\Http\Request;

class AntecedentesPersonalesController extends Controller
{
    // Almacena antecedentes personales
    public function store(Request $request, $pacienteId)
    {
        // Validación de los datos recibidos
        $request->validate([
            'hta_personal' => 'boolean',
            'dbt1_personal' => 'boolean',
            'ca_personal' => 'nullable|string|max:255',
            'dislipidemia_personal' => 'boolean',
            'sobrepeso_obesidad_personal' => 'boolean',
            'alergias' => 'nullable|string|max:255',
            'embarazo' => 'boolean',
            'cirugias_previas' => 'nullable|string|max:255',
        ]);

        $paciente = AntecedentePersonal::create($request->all());

        return redirect()->route('paciente.index')->with('success', 'Antecedentes personales creados con éxito.');
    }
}
