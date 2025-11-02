<?php

namespace App\Http\Controllers;

use App\Models\ParametrosAntropometricos;
use App\Models\Paciente;
use Illuminate\Http\Request;

class ParametrosAntropometricosController extends Controller
{
    // Mostrar el formulario para crear parámetros antropométricos
    public function create($pacienteId)
    {
        $paciente = Paciente::findOrFail($pacienteId);
        return view('parametros.create', compact('paciente')); // Asegúrate de tener la vista 'parametros.create'
    }

    // Almacenar los parámetros antropométricos en la base de datos
    public function store(Request $request, $pacienteId)
    {
        $request->validate([
            'peso' => 'required|numeric',
            'altura' => 'required|numeric',
            'imc' => 'required|numeric',
        ]);

        $paciente = Paciente::findOrFail($pacienteId);

        $parametro = new ParametrosAntropometricos();
        $parametro->paciente_id = $paciente->id;
        $parametro->peso = $request->input('peso');
        $parametro->altura = $request->input('altura');
        $parametro->imc = $request->input('imc');
        $parametro->save();

        return redirect()->route('paciente.show', $pacienteId)->with('success', 'Parámetros antropométricos guardados exitosamente.');
    }

    // Mostrar los parámetros antropométricos de un paciente
    public function show($id)
    {
        $parametro = ParametrosAntropometricos::findOrFail($id);
        return view('parametros.show', compact('parametro')); // Asegúrate de tener la vista 'parametros.show'
    }

    // Mostrar el formulario para editar los parámetros antropométricos
    public function edit($id)
    {
        $parametro = ParametrosAntropometricos::findOrFail($id);
        return view('parametros.edit', compact('parametro')); // Asegúrate de tener la vista 'parametros.edit'
    }

    // Actualizar los parámetros antropométricos en la base de datos
    public function update(Request $request, $id)
    {
        $request->validate([
            'peso' => 'required|numeric',
            'altura' => 'required|numeric',
            'imc' => 'required|numeric',
        ]);

        $parametro = ParametrosAntropometricos::findOrFail($id);
        $parametro->peso = $request->input('peso');
        $parametro->altura = $request->input('altura');
        $parametro->imc = $request->input('imc');
        $parametro->save();

        return redirect()->route('paciente.show', $parametro->paciente_id)->with('success', 'Parámetros antropométricos actualizados exitosamente.');
    }

    // Eliminar los parámetros antropométricos de la base de datos
    public function destroy($id)
    {
        $parametro = ParametrosAntropometricos::findOrFail($id);
        $pacienteId = $parametro->paciente_id;
        $parametro->delete();

        return redirect()->route('paciente.show', $pacienteId)->with('success', 'Parámetros antropométricos eliminados exitosamente.');
    }
}
