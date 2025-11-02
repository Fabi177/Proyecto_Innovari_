<!-- resources/views/auth/login.blade.php -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Historial Clínico Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('images/icono.svg') }}" type="image/x-icon">
</head>
<body class="flex items-center justify-center h-screen bg-blue-100 bg-no-repeat bg-cover" style="background-image: url('{{ asset('images/Fondo_Enfermeria.jpg') }}');">
    <d class="p-8 bg-white rounded-lg shadow-md w-96 backdrop-filter backdrop-blur-lg bg-opacity-80">
    <img src="{{ asset('images/icono.svg') }}" alt="Logo" class="w-auto h-24 mx-auto">
            <h2 class="mt-4 text-2xl font-bold text-gray-800">Iniciar Sesión</h2>
        </div>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block mb-2 text-sm font-bold text-gray-700">Correo Electrónico</label>
                <input id="email" type="email" name="email" required autofocus
                       class="w-full px-3 py-2 leading-tight text-gray-700 border rounded shadow appearance-none focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-6">
                <label for="password" class="block mb-2 text-sm font-bold text-gray-700">Contraseña</label>
                <input id="password" type="password" name="password" required
                       class="w-full px-3 py-2 mb-3 leading-tight text-gray-700 border rounded shadow appearance-none focus:outline-none focus:shadow-outline">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="px-4 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700 focus:outline-none focus:shadow-outline">
                    Ingresar
                </button>
                <a class="inline-block text-sm font-bold text-blue-500 align-baseline hover:text-blue-800" href="#">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        </form>
    </div>
</body>
</html>
