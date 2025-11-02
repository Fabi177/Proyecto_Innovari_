<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Clínico Digital</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0; /* Eliminar márgenes predeterminados */
            padding: 0; /* Eliminar relleno predeterminado */
            height: 100vh; /* Altura total de la ventana */
            display: flex;
            flex-direction: column; /* Para permitir que el header y el main se apilen */
        }
        header {
            background-color: rgba(0, 123, 255, 0.8); /* Color celeste semitransparente */
            padding: 20px;
            color: white;
            display: flex; /* Usar flexbox */
            justify-content: space-between; /* Espacio entre logo y botones */
            align-items: center; /* Centrar verticalmente */
            z-index: 1; /* Asegurarse de que el encabezado esté por encima de la imagen */
        }
        main {
            position: relative; /* Necesario para posicionar la imagen de fondo */
            flex: 1; /* Permitir que main ocupe todo el espacio disponible */
            overflow: hidden; /* Evitar que el contenido se desborde */
        }
        .background-image {
            position: absolute; /* Posicionar la imagen de fondo */
            top: 0;
            left: 0;
            width: 100%; /* Ancho total de la ventana */
            height: 100%; /* Altura total de la ventana */
            background-image: url('{{ asset('images/fondo_welcome.jpg') }}'); /* Ruta de la imagen de fondo */
            background-size: cover; /* Cubrir todo el contenedor */
            background-position: center; /* Centrar la imagen */
            z-index: 0; /* Poner la imagen detrás del contenido */
        }
        h1 {
            font-size: 2.5em;
            margin: 0; /* Eliminar margen para mejor alineación */
            color: #007bff; /* Color celeste */
            position: relative; /* Asegurar que el texto esté por encima de la imagen */
            z-index: 1; /* Asegurarse de que el texto esté por encima de la imagen */
        }
        p {
            margin: 10px 0; /* Espaciado entre párrafos */
            position: relative; /* Asegurar que el texto esté por encima de la imagen */
            z-index: 1; /* Asegurarse de que el texto esté por encima de la imagen */
        }
        a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff; /* Color celeste */
            color: white;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        a:hover {
            background-color: #0056b3; /* Color celeste oscuro */
        }
    </style>
</head>
<body>
    <header>
        <div class="flex justify-center">
            <img src="images/loco_welcome.jpg" alt="Logo" style="width: 90px; height: auto;" />
        </div>
        <div>
            <a href="/login">Iniciar sesión</a>
            <a href="/register">Registrarse</a>
        </div>
    </header>
    <main>
        <div class="background-image"></div> <!-- Imagen de fondo -->
        <h1>Historial Clínico Digital</h1>
        <p id="textoHistorial">Este es el historial clínico por defecto.</p>
        <p id="textoInstrucciones">Instrucciones de uso: Por favor, complete todos los campos necesarios.</p>
    </main>
</body>
</html>
