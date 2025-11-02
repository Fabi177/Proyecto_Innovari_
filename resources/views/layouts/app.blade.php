<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Historial Clínico Digital') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .header-bg {
            background-color: #e6f7ff; /* Fondo azul claro */
            border-bottom: 4px solid #007bff; /* Línea azul más oscura */
        }
        .header-title {
            font-size: 1.75rem; /* Tamaño de fuente más grande */
            color: #007bff; /* Color del título */
            font-weight: bold; /* Texto en negrita */
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-blue-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="shadow header-bg dark:bg-gray-800">
                <div class="flex items-center px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <img src="/images/Central_Clinica.png" alt="Icon" class="w-8 h-8 mr-3" />
                    <h1 class="header-title">{{ $header }}</h1>
                </div>
            </header>
        @endisset

        <!-- Contenido de la Página -->
        <main>
            {{ $slot }}
        </main>
    </div>
</body>
</html>
