<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-center text-gray-800">
            {{ __('Detalles del Paciente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Contenedor de tarjetas en un flex -->
            <div class="flex flex-wrap justify-center gap-8 p-6 bg-white shadow-xl sm:rounded-lg dark:bg-gray-50">

                <!-- Datos Generales del Paciente -->
                <div class="p-6 bg-gray-100 border rounded-lg shadow-md w-80">
                    <h3 class="mb-4 text-lg font-semibold text-center text-gray-800 dark:text-gray-900">Datos Generales</h3>
                    <p><strong>Nombre Completo:</strong> {{ $paciente->nombre_completo }}</p>
                    <p><strong>Apellido:</strong> {{ $paciente->apellido }}</p>
                    <p><strong>Fecha de Nacimiento:</strong> {{ $paciente->fecha_nacimiento }}</p>
                    <p><strong>Edad Actual:</strong> {{ $paciente->edad }}</p>
                    <p><strong>Estado Civil:</strong> {{ $paciente->estado_civil }}</p>
                </div>

                <!-- Antecedentes Familiares -->
                <div class="p-6 bg-gray-100 border rounded-lg shadow-md w-80">
                    <h3 class="mb-4 text-lg font-semibold text-center text-gray-800 dark:text-gray-900">Antecedentes Familiares</h3>
                    <p><strong>HTA:</strong> {{ $paciente->antecedentesFamiliares->hipertension ? 'Sí' : 'No' }}</p>
                    <p><strong>Diabetes Tipo 2:</strong> {{ $paciente->antecedentesFamiliares->diabetes ? 'Sí' : 'No' }}</p>
                    <p><strong>Cáncer:</strong> {{ $paciente->antecedentesFamiliares->cancer }}</p>
                    <p><strong>Obesidad:</strong> {{ $paciente->antecedentesFamiliares->obesidad ? 'Sí' : 'No' }}</p>
                    <p><strong>Dislipidemia:</strong> {{ $paciente->antecedentesFamiliares->dislipidemia ? 'Sí' : 'No' }}</p>
                    <p><strong>Otros:</strong> {{ $paciente->antecedentesFamiliares->otros }}</p>
                </div>

                <!-- Antecedentes Personales -->
                <div class="p-6 bg-gray-100 border rounded-lg shadow-md w-80">
                    <h3 class="mb-4 text-lg font-semibold text-center text-gray-800 dark:text-gray-900">Antecedentes Personales</h3>
                    <p><strong>HTA:</strong> {{ $paciente->antecedentesPersonales->hipertension ? 'Sí' : 'No' }}</p>
                    <p><strong>Diabetes Tipo 1:</strong> {{ $paciente->antecedentesPersonales->diabetes_tipo_1 ? 'Sí' : 'No' }}</p>
                    <p><strong>Cáncer:</strong> {{ $paciente->antecedentesPersonales->cancer }}</p>
                    <p><strong>Obesidad:</strong> {{ $paciente->antecedentesPersonales->obesidad ? 'Sí' : 'No' }}</p>
                    <p><strong>Dislipidemia:</strong> {{ $paciente->antecedentesPersonales->dislipidemia ? 'Sí' : 'No' }}</p>
                    <p><strong>Alergias:</strong> {{ $paciente->antecedentesPersonales->alergias }}</p>
                </div>

                <!-- Parámetros Antropométricos -->
                <div class="p-6 bg-gray-100 border rounded-lg shadow-md w-80">
                    <h3 class="mb-4 text-lg font-semibold text-center text-gray-800 dark:text-gray-900">Parámetros Antropométricos</h3>
                    <p><strong>Peso (kg):</strong> {{ $paciente->parametrosAntropometricos->peso }}</p>
                    <p><strong>Altura (cm):</strong> {{ $paciente->parametrosAntropometricos->altura }}</p>
                    <p><strong>IMC:</strong> {{ $paciente->parametrosAntropometricos->imc }}</p>
                </div>

                <!-- Signos Vitales -->
                <div class="p-6 bg-gray-100 border rounded-lg shadow-md w-80">
                    <h3 class="mb-4 text-lg font-semibold text-center text-gray-800 dark:text-gray-900">Signos Vitales</h3>
                    <p><strong>Presión Arterial:</strong> {{ $paciente->signosVitales->presion_arterial ?? 'N/A' }}</p>
                    <p><strong>Frecuencia Cardíaca:</strong> {{ $paciente->signosVitales->frecuencia_cardiaca ?? 'N/A' }}</p>
                    <p><strong>Frecuencia Respiratoria:</strong> {{ $paciente->signosVitales->frecuencia_respiratoria ?? 'N/A' }}</p>
                    <p><strong>Temperatura (°C):</strong> {{ $paciente->signosVitales->temperatura ?? 'N/A' }}</p>
                    <p><strong>Saturación de Oxígeno (%):</strong> {{ $paciente->signosVitales->saturacion_oxigeno ?? 'N/A' }}</p>
                </div>

            </div>

            <!-- Botón para regresar a la lista de pacientes -->
            <div class="flex justify-center mt-8">
                <a href="{{ route('paciente.index') }}" class="inline-flex items-center px-6 py-3 text-white transition duration-300 bg-green-600 rounded-md shadow-lg hover:bg-green-800">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"></path>
                    </svg>
                    Ver lista de pacientes
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
