<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-center text-gray-800 ">
            {{ __('Crear Nuevo Paciente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg dark:bg-gray-50">
                <div class="p-8 bg-[#f5f5dc] border-b border-gray-200 dark:bg-[#b3d9ff] dark:border-gray-400">
                    <form action="{{ route('paciente.store') }}" method="POST" class="space-y-10">
                        @csrf

                        <!-- Datos Generales del Paciente -->
                        <section>
                            <h3 class="mb-6 text-2xl font-semibold text-center text-gray-800 dark:text-gray-900">Datos Generales del Paciente</h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <x-input-group label="Nombre" name="nombre_completo" required />
                                <x-input-group label="Apellido" name="apellido" required />
                                <x-input-group label="Fecha de Nacimiento" name="fecha_nacimiento" id="fecha_nacimiento" type="date" required onchange="calcularEdad()" />
                                <x-input-group label="Edad Actual" name="edad" id="edad_actual" type="number" required />
                                <x-select-group label="Sexo" name="sexo" :options="['Masculino', 'Femenino']" required />
                                <x-select-group label="Estado Civil" name="estado_civil" :options="['Soltero/a', 'Casado/a', 'Divorciado/a', 'Viudo/a']" required />
                                <x-input-group label="Lugar de Origen" name="lugar_origen" required />
                                <x-select-group label="Nivel de Estudio" name="nivel_estudio" :options="['Primario', 'Secundario', 'Terciario', 'Universitario']" required />
                                <x-input-group label="Ocupación Actual" name="ocupacion" required />
                                <x-input-group label="Años que Trabaja en este Puesto" name="anos_puesto" type="number" required />
                            </div>
                        </section>

                        <!-- Antecedentes Familiares -->
                        <sectiontion>
                            <h3 class="mb-6 text-2xl font-semibold text-center text-gray-800 dark:text-gray-900">Antecedentes Familiares</h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <x-checkbox-group label="Hipertensión Arterial (HTA)" name="hta_familiar" />
                                <x-checkbox-group label="Diabetes Mellitus Tipo 2 (DBT II)" name="dbt_familiar" />
                                <x-input-group label="Cáncer (CA)" name="ca_familiar" placeholder="Especificar" />
                                <x-checkbox-group label="Sobrepeso/Obesidad" name="sobrepeso_obesidad_familiar" />
                                <x-checkbox-group label="Dislipidemia" name="dislipidemia_familiar" />
                                <x-input-group label="Otros" name="otros_familiares" placeholder="Especificar" />
                            </div>
                        </sectiontion>
                        <!-- Antecedentes Personales -->
                        <section>
                            <h3 class="mb-6 text-2xl font-semibold text-center text-gray-800 dark:text-gray-900">Antecedentes Personales</h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <x-checkbox-group label="Hipertensión Arterial (HTA)" name="hta_personal" />
                                <x-checkbox-group label="Diabetes Mellitus Tipo 1 (DBT I)" name="dbt1_personal" />
                                <x-input-group label="Cáncer (CA)" name="ca_personal" placeholder="Especificar" />
                                <x-checkbox-group label="Dislipidemias" name="dislipidemia_personal" />
                                <x-checkbox-group label="Sobrepeso/Obesidad" name="sobrepeso_obesidad_personal" />
                                <x-input-group label="Alergias" name="alergias" placeholder="Especificar" />
                                <x-checkbox-group label="Embarazo" name="embarazo" />
                                <x-input-group label="Cirugías Previas" name="cirugias_previas" placeholder="Especificar" />
                            </div>
                        </section>

                        <!-- Parámetros Antropométricos -->
                        <section>
                            <h3 class="mb-6 text-2xl font-semibold text-center text-gray-800 dark:text-gray-900">Parámetros Antropométricos</h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <x-input-group label="Peso (kg)" name="peso" id="peso" type="number" required />
                                <x-input-group label="Altura (cm)" name="altura" id="altura" type="number" required />
                                <x-input-group label="Índice de Masa Corporal (IMC)" name="imc" id="imc" type="number" readonly />
                            </div>
                        </section>

                        <!-- Signos Vitales -->
                        <section>
                            <h3 class="mb-6 text-2xl font-semibold text-center text-gray-800 dark:text-gray-900">Signos Vitales</h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <x-input-group label="Presión Arterial (mmHg)" name="presion_arterial" placeholder="Ej: 120/80" required />
                                <x-input-group label="Frecuencia Cardíaca (lpm)" name="frecuencia_cardiaca" placeholder="Ej: 70" required />
                                <x-input-group label="Frecuencia Respiratoria (rpm)" name="frecuencia_respiratoria" placeholder="Ej: 18" required />
                                <x-input-group label="Temperatura (°C)" name="temperatura" placeholder="Ej: 36.5" required />
                                <x-input-group label="Saturación de Oxígeno (%)" name="saturacion_oxigeno" placeholder="Ej: 98" required />
                            </div>
                        </section>


                        <!-- Botón Guardar -->
                        <div class="flex justify-center mt-10">
                            <button type="submit" class="px-6 py-3 text-lg font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Guardar
                            </button>
                            @if ($errors)
                            {{$errors}}
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const pesoInput = document.getElementById('peso');
            const alturaInput = document.getElementById('altura');
            const imcInput = document.getElementById('imc');

            function calcularIMC() {
                const peso = parseFloat(pesoInput.value);
                const altura = parseFloat(alturaInput.value) / 100; // Convertir cm a metros

                if (peso > 0 && altura > 0) {
                    const imc = (peso / (altura * altura)).toFixed(2); // Cálculo del IMC
                    imcInput.value = imc; // Mostrar el IMC calculado
                } else {
                    imcInput.value = ''; // Limpiar el campo IMC si no son válidos
                }
            }

            pesoInput.addEventListener('input', calcularIMC);
            alturaInput.addEventListener('input', calcularIMC);
        });

        function calcularEdad() {
            const fechaNacimientoInput = document.getElementById('fecha_nacimiento');
            const edadInput = document.getElementById('edad');

            const fechaNacimiento = new Date(fechaNacimientoInput.value);
            const hoy = new Date();

            if (fechaNacimientoInput.value) {
                let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
                const mes = hoy.getMonth() - fechaNacimiento.getMonth();
                if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                    edad--;
                }
                edadInput.value = edad; // Mostrar la edad calculada
            } else {
                edadInput.value = ''; // Limpiar el campo de edad si no es válida
            }
        }
    </script>
</x-app-layout>
