<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Editar Paciente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 bg-gray-50">
                    <h3 class="mb-4 text-lg font-semibold text-blue-900">Formulario de edición de paciente</h3>

                    <!-- Formulario para editar paciente -->
                    <form action="{{ route('paciente.update', $paciente->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Campo: Nombre Completo -->
                            <div class="mb-4">
                                <label for="nombre_completo" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                                <input type="text" id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo', $paciente->nombre_completo) }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>

                            <!-- Campo: Apellido -->
                            <div class="mb-4">
                                <label for="apellido" class="block text-sm font-medium text-gray-700">Apellido</label>
                                <input type="text" id="apellido" name="apellido" value="{{ old('apellido', $paciente->apellido) }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>

                            <!-- Campo: Fecha de Nacimiento -->
                            <div class="mb-4">
                                <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700">Fecha de Nacimiento</label>
                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $paciente->fecha_nacimiento) }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>

                            <!-- Campo: Sexo -->
                            <div class="mb-4">
                                <label for="sexo" class="block text-sm font-medium text-gray-700">Sexo</label>
                                <select id="sexo" name="sexo" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    <option value="Masculino" {{ old('sexo', $paciente->sexo) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                    <option value="Femenino" {{ old('sexo', $paciente->sexo) == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                    <option value="Otro" {{ old('sexo', $paciente->sexo) == 'Otro' ? 'selected' : '' }}>Otro</option>
                                </select>
                            </div>

                            <!-- Campo: Estado Civil -->
                            <div class="mb-4">
                                <label for="estado_civil" class="block text-sm font-medium text-gray-700">Estado Civil</label>
                                <select id="estado_civil" name="estado_civil" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    <option value="Soltero/a" {{ old('estado_civil', $paciente->estado_civil) == 'Soltero/a' ? 'selected' : '' }}>Soltero/a</option>
                                    <option value="Casado/a" {{ old('estado_civil', $paciente->estado_civil) == 'Casado/a' ? 'selected' : '' }}>Casado/a</option>
                                    <option value="Divorciado/a" {{ old('estado_civil', $paciente->estado_civil) == 'Divorciado/a' ? 'selected' : '' }}>Divorciado/a</option>
                                    <option value="Viudo/a" {{ old('estado_civil', $paciente->estado_civil) == 'Viudo/a' ? 'selected' : '' }}>Viudo/a</option>
                                </select>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex justify-end mt-4 space-x-4">
                            <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-800">Guardar Cambios</button>
                            <a href="{{ route('paciente.index') }}" class="px-4 py-2 text-white bg-gray-600 rounded-md hover:bg-gray-800">Cancelar</a>
                        </div>
                    </form>

                    <!-- Sección de Antecedentes Familiares -->
                    <div class="mt-6">
                        <div class="bg-white border border-gray-300 rounded-lg shadow-sm">
                            <div class="flex items-center justify-between p-4">
                                <h4 class="text-lg font-medium text-gray-700">Antecedentes Familiares</h4>
                                <button class="font-semibold text-blue-600 hover:text-blue-800" data-toggle="accordion" data-target="#antecedentesFamiliares">
                                    <i class="mr-2 fas fa-edit"></i>Modificar
                                </button>
                            </div>
                            <div id="antecedentesFamiliares" class="hidden p-4">
                                <form action="{{ route('antecedentes.familiares.update', $paciente->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-4">
                                        <!-- Cargar datos actuales -->
                                        <div>
                                            <label for="hipertension" class="block text-gray-700">Hipertensión</label>
                                            <input type="checkbox" id="hipertension" name="hipertension" {{ $paciente->antecedentesFamiliares->hipertension ? 'checked' : '' }} class="w-5 h-5 text-blue-600">
                                        </div>
                                        <div>
                                            <label for="diabetes" class="block text-gray-700">Diabetes</label>
                                            <input type="checkbox" id="diabetes" name="diabetes" {{ $paciente->antecedentesFamiliares->diabetes ? 'checked' : '' }} class="w-5 h-5 text-blue-600">
                                        </div>
                                        <div>
                                            <label for="cancer" class="block text-gray-700">Cáncer</label>
                                            <input type="checkbox" id="cancer" name="cancer" {{ $paciente->antecedentesFamiliares->cancer ? 'checked' : '' }} class="w-5 h-5 text-blue-600">
                                        </div>
                                        <div>
                                            <label for="obesidad" class="block text-gray-700">Obesidad</label>
                                            <input type="checkbox" id="obesidad" name="obesidad" {{ $paciente->antecedentesFamiliares->obesidad ? 'checked' : '' }} class="w-5 h-5 text-blue-600">
                                        </div>
                                        <div>
                                            <label for="dislipidemia" class="block text-gray-700">Dislipidemia</label>
                                            <input type="checkbox" id="dislipidemia" name="dislipidemia" {{ $paciente->antecedentesFamiliares->dislipidemia ? 'checked' : '' }} class="w-5 h-5 text-blue-600">
                                        </div>
                                        <button type="submit" class="px-4 py-2 mt-4 text-white bg-blue-600 rounded-lg">Guardar Cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Repite el mismo patrón para Antecedentes Personales, Parámetros Antropométricos y Signos Vitales -->

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
