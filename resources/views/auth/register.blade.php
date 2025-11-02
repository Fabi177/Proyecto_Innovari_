<x-guest-layout>
    <!-- Background container -->
    <div class="flex items-center justify-center min-h-screen" style="background-image: url('/images/Fondo_Formulario.jpg'); background-size: cover; background-position: center;">
        <div class="w-full max-w-md p-6 bg-white bg-opacity-50 rounded-lg shadow-lg">
            <!-- Title -->
            <h1 class="mb-6 text-2xl font-bold text-center text-gray-700">{{ __('Registro de Enfermero') }}</h1>

            <!-- Form -->
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <x-input-label for="name" :value="__('Nombre')" class="block text-sm font-medium text-gray-700" />
                    <x-text-input id="name"
                        class="block w-full mt-1 text-black bg-black border border-gray-300 rounded-md shadow-sm bg-opacity-10 border-opacity-20 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Last Name -->
                <div class="mb-4">
                    <x-input-label for="last_name" :value="__('Apellido')" class="block text-sm font-medium text-gray-700" />
                    <x-text-input id="last_name"
                        class="block w-full mt-1 text-black bg-black border border-gray-300 rounded-md shadow-sm bg-opacity-10 border-opacity-20 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        type="text" name="last_name" :value="old('last_name')" required autofocus autocomplete="family-name" />
                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <x-input-label for="email" :value="__('Correo Electrónico')" class="block text-sm font-medium text-gray-700" />
                    <x-text-input id="email"
                        class="block w-full mt-1 text-black bg-black border border-gray-300 rounded-md shadow-sm bg-opacity-10 border-opacity-20 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <x-input-label for="password" :value="__('Contraseña')" class="block text-sm font-medium text-gray-700" />
                    <x-text-input id="password"
                        class="block w-full mt-1 text-black bg-black border border-gray-300 rounded-md shadow-sm bg-opacity-10 border-opacity-20 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" class="block text-sm font-medium text-gray-700" />
                    <x-text-input id="password_confirmation"
                        class="block w-full mt-1 text-black bg-black border border-gray-300 rounded-md shadow-sm bg-opacity-10 border-opacity-20 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- Register Button -->
                <div class="flex items-center justify-end mt-4">
                    <a class="text-sm text-gray-600 underline rounded-md dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                        href="{{ route('login') }}">
                        {{ __('Already registered?') }}
                    </a>

                    <x-primary-button class="ms-4">
                        {{ __('Register') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
