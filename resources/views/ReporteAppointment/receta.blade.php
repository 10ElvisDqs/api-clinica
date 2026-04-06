<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta Médica - Clínica Nuestra Señora del Rosario</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 5px 0;
            color: #333;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .logo {
            max-width: 100px;
            margin-bottom: 10px;
        }
        .content {
            margin-top: 20px;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        .background-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.5; /* Ajusta la opacidad de la imagen de fondo */
            filter: blur(0px); /* Elimina el desenfoque */
        }
        .appointment {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        .appointment h2 {
            margin-top: 0;
            color: #333;
            font-size: 22px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .medicamentos {
            margin-top: 10px;
        }
        .medicamento {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .medicamento:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }
        .medicamento p {
            margin: 5px 0;
            color: #666;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img class="logo" src="{{ public_path('img/logo.jpg') }}" alt="Logo">
            <h1>Clínica Nuestra Señora del Rosario</h1>
            <p>Receta Médica</p>
        </div>
        <div class="content">
            <img class="background-img" src="{{ public_path('img/login-02.jpg') }}" alt="Background">
            @foreach($appointmentAttentions as $attention)
            <div class="appointment">
                <h2>Atención #{{ $attention->id }}</h2>
                <p><strong>ID de Cita:</strong> {{ $attention->appointment_id }}</p>
                <p><strong>ID de Paciente:</strong> {{ $attention->patient_id }}</p>
                <p><strong>Descripción:</strong> {{ $attention->description }}</p>
                <div class="medicamentos">
                    <p><strong>Medicamentos Recetados:</strong></p>
                    @foreach($medicamentos as $medicamento)
                    <div class="medicamento">
                        <p><strong>Nombre del Medicamento:</strong> {{ $medicamento['name_medical'] }}</p>
                        <p><strong>Uso:</strong> {{ $medicamento['uso'] }}</p>
                    </div>
                    @endforeach
                </div>
                <p><strong>Fecha de Creación:</strong> {{ $attention->created_at }}</p>
            </div>
            @endforeach
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Clínica Nuestra Señora del Rosario. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
