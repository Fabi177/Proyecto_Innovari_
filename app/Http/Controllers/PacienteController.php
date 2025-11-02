<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PacienteActualizado;
use App\Models\SignosVitales;
use App\Models\AntecedentesFamiliares;
use App\Models\AntecedentesPersonales;
use App\Models\ParametrosAntropometricos;

class PacienteController extends Controller
{
    // Muestra la lista de pacientes
    public function index() {
        // Obtener todos los pacientes
        $pacientes = Paciente::all();

        // Obtener el último paciente registrado
        $ultimoPaciente = Paciente::latest()->first();

        // Obtener el total de pacientes
        $totalPacientes = Paciente::count();

        // Retornar la vista con los pacientes, el total y el último registrado
        return view('pacientes.index', compact('pacientes', 'totalPacientes', 'ultimoPaciente'));
    }


    // Muestra el formulario para crear un nuevo paciente
    public function create() {
        return view('pacientes.create'); // Retorna la vista para crear un paciente
    }

    // Almacena un nuevo paciente
    public function store(Request $request) {
       // Validación de los datos recibidos
       $request->validate([
            // Datos Generales del Paciente
            'nombre_completo' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'edad' => 'nullable|integer|min:0',
            'sexo' => 'required|string|max:10',
            'estado_civil' => 'required|string|max:20',
            'lugar_origen' => 'required|string|max:255',
            'nivel_estudio' => 'required|string|max:255',
            'ocupacion' => 'required|string|max:255',
            'anos_puesto' => 'required|integer',

            // Antecedentes Familiares
            'hta_familiar' => 'nullable|boolean',
            'dbt_familiar' => 'nullable|boolean',
            'ca_familiar' => 'nullable|string|max:255',
            'sobrepeso_obesidad_familiar' => 'nullable|boolean',
            'dislipidemia_familiar' => 'nullable|boolean',
            'otros_familiares' => 'nullable|string|max:255',

            // Antecedentes Personales
            'hta_personal' => 'nullable|boolean',
            'dbt1_personal' => 'nullable|boolean',
            'ca_personal' => 'nullable|string|max:255',
            'dislipidemia_personal' => 'nullable|boolean',
            'sobrepeso_obesidad_personal' => 'nullable|boolean',
            'alergias' => 'nullable|string|max:255',
            'embarazo' => 'nullable|boolean',
            'cirugias_previas' => 'nullable|string|max:255',

            // Parámetros Antropométricos
            'peso' => 'required|numeric|between:0,999.99',
            'altura' => 'required|numeric|between:0,300.00',
            'imc' => 'nullable|numeric|between:0,100.00',

            // Signos Vitales
            'presion_arterial' => 'required|string|max:7',
            'frecuencia_cardiaca' => 'required|integer|min:0',
            'frecuencia_respiratoria' => 'required|integer|min:0',
            'temperatura' => 'required|numeric|between:0,50.00',
            'saturacion_oxigeno' => 'required|numeric|between:0,100.00'
       ]);

       // Crear el paciente
       $paciente = Paciente::create([
           'nombre_completo' => $request['nombre_completo'],
           'apellido' => $request['apellido'],
           'fecha_nacimiento' => $request['fecha_nacimiento'],
           'edad' => $request['edad'],
           'sexo' => $request['sexo'],
           'estado_civil' => $request['estado_civil'],
           'lugar_origen' => $request['lugar_origen'],
           'nivel_estudio' => $request['nivel_estudio'],
           'ocupacion' => $request['ocupacion'],
           'anos_puesto' => $request['anos_puesto'],
       ]);

       // Crear los registros relacionados
       AntecedentesFamiliares::create([
           'paciente_id' => $paciente->id,
           'hipertension' => $request['hta_familiar'],
           'diabetes' => $request['dbt_familiar'],
           'cancer' => $request['ca_familiar'],
           'obesidad' => $request['sobrepeso_obesidad_familiar'],
           'dislipidemia' => $request['dislipidemia_familiar'],
           'otros' => $request['otros_familiares'],
       ]);

       AntecedentesPersonales::create([
           'paciente_id' => $paciente->id,
           'hipertension' => $request['hta_personal'],
           'diabetes_tipo_1' => $request['dbt1_personal'],
           'cancer' => $request['ca_personal'],
           'dislipidemia' => $request['dislipidemia_personal'],
           'obesidad' => $request['sobrepeso_obesidad_personal'],
           'alergias' => $request['alergias'],
           'embarazo' => $request['embarazo'],
           'cirugias_previas' => $request['cirugias_previas'],
       ]);

       ParametrosAntropometricos::create([
           'paciente_id' => $paciente->id,
           'peso' => $request['peso'],
           'altura' => $request['altura'],
           'imc' => $request['imc'],
       ]);

       SignosVitales::create([
           'paciente_id' => $paciente->id,
           'presion_arterial' => $request['presion_arterial'],
           'frecuencia_cardiaca' => $request['frecuencia_cardiaca'],
           'frecuencia_respiratoria' => $request['frecuencia_respiratoria'],
           'temperatura' => $request['temperatura'],
           'saturacion_oxigeno' => $request['saturacion_oxigeno'],
       ]);

       // Enviar correo al crear el paciente (opcional)
       // Mail::to('araujoivansan@gmail.com')->send(new PacienteActualizado($paciente->nombre_completo));

       // Redirigir a la lista de pacientes con un mensaje de éxito
       return redirect()->route('paciente.index')->with('success', 'Paciente creado con éxito.');
    }

    // Muestra los detalles de un paciente específico
    public function show($id) {
        // Cargar el paciente con todas sus relaciones
        $paciente = Paciente::with([
            'antecedentesFamiliares',
            'antecedentesPersonales',
            'parametrosAntropometricos',
            'signosVitales'
        ])->findOrFail($id);

        return view('pacientes.show', compact('paciente'));
    }

    // Muestra el formulario para editar un paciente existente
    public function edit($id)
{
    $paciente = Paciente::findOrFail($id);
    return view('pacientes.edit', compact('paciente'));
}

    // Actualiza un paciente existente
    // Actualiza un paciente existente
public function update(Request $request, $id)
{
    // Validar los datos recibidos
    $request->validate([
        'nombre_completo' => 'required|string|max:255',
        'apellido' => 'required|string|max:255',
        'fecha_nacimiento' => 'required|date',
        'edad' => 'nullable|integer|min:0',
        'sexo' => 'required|string|max:10',
        'estado_civil' => 'required|string|max:20',
        'lugar_origen' => 'required|string|max:255',
        'nivel_estudio' => 'required|string|max:255',
        'ocupacion' => 'required|string|max:255',
        'anos_puesto' => 'required|integer',

        // Antecedentes Familiares
        'hta_familiar' => 'nullable|boolean',
        'dbt_familiar' => 'nullable|boolean',
        'ca_familiar' => 'nullable|string|max:255',
        'sobrepeso_obesidad_familiar' => 'nullable|boolean',
        'dislipidemia_familiar' => 'nullable|boolean',
        'otros_familiares' => 'nullable|string|max:255',

        // Antecedentes Personales
        'hta_personal' => 'nullable|boolean',
        'dbt1_personal' => 'nullable|boolean',
        'ca_personal' => 'nullable|string|max:255',
        'dislipidemia_personal' => 'nullable|boolean',
        'sobrepeso_obesidad_personal' => 'nullable|boolean',
        'alergias' => 'nullable|string|max:255',
        'embarazo' => 'nullable|boolean',
        'cirugias_previas' => 'nullable|string|max:255',

        // Parámetros Antropométricos
        'peso' => 'required|numeric|between:0,999.99',
        'altura' => 'required|numeric|between:0,300.00',
        'imc' => 'nullable|numeric|between:0,100.00',

        // Signos Vitales
        'presion_arterial' => 'required|string|max:7',
        'frecuencia_cardiaca' => 'required|integer|min:0',
        'frecuencia_respiratoria' => 'required|integer|min:0',
        'temperatura' => 'required|numeric|between:0,50.00',
        'saturacion_oxigeno' => 'required|numeric|between:0,100.00'
    ]);

    // Encuentra al paciente por su ID
    $paciente = Paciente::findOrFail($id);

    // Actualiza los datos generales del paciente
    $paciente->update([
        'nombre_completo' => $request['nombre_completo'],
        'apellido' => $request['apellido'],
        'fecha_nacimiento' => $request['fecha_nacimiento'],
        'edad' => $request['edad'],
        'sexo' => $request['sexo'],
        'estado_civil' => $request['estado_civil'],
        'lugar_origen' => $request['lugar_origen'],
        'nivel_estudio' => $request['nivel_estudio'],
        'ocupacion' => $request['ocupacion'],
        'anos_puesto' => $request['anos_puesto'],
    ]);

    // Actualiza los antecedentes familiares
    $paciente->antecedentesFamiliares()->update([
        'hipertension' => $request['hta_familiar'],
        'diabetes' => $request['dbt_familiar'],
        'cancer' => $request['ca_familiar'],
        'obesidad' => $request['sobrepeso_obesidad_familiar'],
        'dislipidemia' => $request['dislipidemia_familiar'],
        'otros' => $request['otros_familiares'],
    ]);

    // Actualiza los antecedentes personales
    $paciente->antecedentesPersonales()->update([
        'hipertension' => $request['hta_personal'],
        'diabetes_tipo_1' => $request['dbt1_personal'],
        'cancer' => $request['ca_personal'],
        'dislipidemia' => $request['dislipidemia_personal'],
        'obesidad' => $request['sobrepeso_obesidad_personal'],
        'alergias' => $request['alergias'],
        'embarazo' => $request['embarazo'],
        'cirugias_previas' => $request['cirugias_previas'],
    ]);

    // Actualiza los parámetros antropométricos
    $paciente->parametrosAntropometricos()->update([
        'peso' => $request['peso'],
        'altura' => $request['altura'],
        'imc' => $request['imc'],
    ]);

    // Actualiza los signos vitales
    $paciente->signosVitales()->update([
        'presion_arterial' => $request['presion_arterial'],
        'frecuencia_cardiaca' => $request['frecuencia_cardiaca'],
        'frecuencia_respiratoria' => $request['frecuencia_respiratoria'],
        'temperatura' => $request['temperatura'],
        'saturacion_oxigeno' => $request['saturacion_oxigeno'],
    ]);

    // Redirige a la lista de pacientes con un mensaje de éxito
    return redirect()->route('paciente.index')->with('success', 'Paciente actualizado correctamente');
}


    // Elimina un paciente existente y sus registros relacionados
    public function destroy(Paciente $paciente) {
        AntecedentesFamiliares::where('paciente_id', $paciente->id)->delete();
        AntecedentesPersonales::where('paciente_id', $paciente->id)->delete();
        ParametrosAntropometricos::where('paciente_id', $paciente->id)->delete();
        SignosVitales::where('paciente_id', $paciente->id)->delete();

        $paciente->delete();

        // Redirige a la lista de pacientes con un mensaje de éxito
        return redirect()->route('paciente.index')->with('success', 'Paciente y registros relacionados eliminados con éxito.');
    }

    // Método para buscar pacientes por nombre
    public function buscarPorNombre(Request $request) {
        $nombre = $request->get('nombre');
        $pacientes = Paciente::where('nombre_completo', 'LIKE', "%{$nombre}%")->get();

        return view('pacientes.index', compact('pacientes'));
    }


}
