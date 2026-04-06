<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Citas</title>

    <link rel="stylesheet" href="{{public_path('css/reporte.css')}}">
</head>
<body>
    {{-- <pre>
        {{ dd($resultado) }} --}}
        {{-- {{ json_encode($resultado, JSON_PRETTY_PRINT) }} --}}
    {{-- </pre> --}}
    <div id="header">
        <img class="logo" src="{{ public_path('img/logo.jpg') }}" alt="Logo">
        <img class="background-img" src="{{ public_path('img/login-02.jpg') }}" alt="Background">
        {{-- <div class="titulo">Reporte de Citas</div>k --}}
    </div>

    <div class="texto-centrado">
        <h1 class="titulo">Reporte de Citas</h1>
    </div>

    <table>
        <thead>
            <tr>
                <th class="texto-centrado">ID</th>
                <th class="texto-centrado">Doctor</th>
                <th class="texto-centrado">Especialidad</th>
                <th class="texto-centrado">Fecha</th>
                <th class="texto-centrado">Paciente</th>
                <th class="texto-centrado">Hora</th>
                <th class="texto-centrado">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resultado['data'] as $appointment)
                <tr>
                    <td>{{ $appointment['id'] }}</td>
                    <td>{{ $appointment['doctor']['full_name'] }}</td>
                    <td>{{ $appointment['specialitie']['name'] }}</td>
                    <td>{{ $appointment['date_appointment_format'] }}</td>
                    <td>{{ $appointment['patient']['full_name'] }}</td>
                    <td>{{ $appointment['segment_hour']['format_segment']['format_hour_start'] }} - {{ $appointment['segment_hour']['format_segment']['format_hour_end'] }}</td>
                    <td class="texto-centrado">
                        @if ($appointment['status'] == 2)
                            <span class="badge text-bg-success">Atendido</span>
                        @elseif ($appointment['status'] == 1)
                            <span class="badge text-bg-ranger">Pendiente</span>
                        @else
                            Desconocido
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer fijo -->
    <div id="footer">
        <img class="logo" src="{{ public_path('img/logo.jpg') }}" alt="Logo">
        <p>&copy; 2024 Clínica Médica</p>
    </div>
</body>
</html>
