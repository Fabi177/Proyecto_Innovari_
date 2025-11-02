<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Lista de Pacientes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 bg-gray-50">

                    <!-- Mostrar total de pacientes con un diseño más atractivo -->
                    <div class="p-6 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <h4 class="mb-2 text-lg font-semibold text-gray-700">Estadísticas de Pacientes</h4>
                        <p class="text-xl font-bold text-gray-900">Total de pacientes: <span class="text-indigo-600">{{ $totalPacientes }}</span></p>
                    </div>

                    <!-- Botón para crear un nuevo paciente -->
                    <div class="mb-4">
                        <a href="{{ route('paciente.create') }}" class="inline-flex items-center px-4 py-2 text-white transition duration-300 bg-red-600 rounded-md shadow hover:bg-red-800">
                            <img src="{{ asset('../../../images/dashboeard.png') }}" alt="Crear nuevo paciente" class="w-6 h-6 mr-2" />
                            Crear nuevo paciente
                        </a>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nombre y Apellido</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Fecha de Nacimiento</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Edad</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Sexo</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Estado Civil</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pacientes as $paciente)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $paciente->nombre_completo }} {{ $paciente->apellido }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $paciente->edad }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $paciente->sexo }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $paciente->estado_civil }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('paciente.show', $paciente->id) }}" class="text-blue-600 hover:text-blue-900">Ver</a>
                                        <a href="{{ route('paciente.edit', $paciente->id) }}" class="ml-2 text-yellow-600 hover:text-yellow-900">Editar</a>
                                        <form action="{{ route('paciente.destroy', $paciente->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-2 text-red-600 hover:text-red-900" onclick="return confirm('¿Estás seguro de eliminar este paciente?');">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Botón para volver a la lista de pacientes -->
                    <div class="mt-4">
                        <a href="{{ route('paciente.index') }}" class="inline-flex items-center px-6 py-3 text-white transition duration-300 bg-green-600 rounded-md shadow hover:bg-green-800">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"></path>
                            </svg>
                            Ver lista de pacientes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
