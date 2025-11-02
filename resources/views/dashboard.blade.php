<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Gestión de Pacientes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 bg-gray-50">
                    <h3 class="mb-4 text-lg font-semibold text-blue-900">Panel de Vista de Pacientes</h3>

                    <!-- Botón para crear un nuevo paciente con icono de usuario -->
                    <div class="mb-4">
                        <a href="{{ route('paciente.create') }}" class="inline-flex items-center px-4 py-2 text-white transition duration-300 bg-red-600 rounded-md shadow hover:bg-red-800">
                            <img src="{{ asset('../../../images/dashboeard.png') }}" alt="Crear nuevo paciente" class="w-6 h-6 mr-2" />
                            Crear nuevo paciente
                        </a>
                    </div>

                    <!-- Botón para ver la lista de pacientes con icono de lista -->
                    <div class="mb-4">
                        <a href="{{ route('paciente.index') }}" class="inline-flex items-center px-6 py-3 text-white transition duration-300 bg-green-600 rounded-md shadow hover:bg-green-800">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"></path>
                            </svg>
                            Ver lista de pacientes
                        </a>
                    </div>

                    <!-- Sección adicional para visualizar las estadísticas o algo más relevante -->
                    <div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-2">
                        <!-- Estadísticas de pacientes -->
                        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <h4 class="mb-3 text-lg font-medium text-gray-700">Estadísticas de Pacientes</h4>
                            <p class="text-gray-500">Total pacientes: <strong>{{ $totalPacientes }}</strong></p>
                            <p class="text-gray-500">
                                Último registrado:
                                <strong>{{ $ultimoPaciente ? $ultimoPaciente->nombre_completo . ' ' . $ultimoPaciente->apellido : 'No disponible' }}</strong>
                            </p>
                        </div>

                        <!-- Otra sección para cualquier información adicional -->
                        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <h4 class="mb-3 text-lg font-medium text-gray-700">Notas recientes</h4>
                            <p class="text-gray-500">Revisiones médicas pendientes: <strong>5</strong></p>
                            <p class="text-gray-500">Próxima cita: <strong>15/10/2024</strong></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
