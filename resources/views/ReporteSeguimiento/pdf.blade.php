<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Seguimientos</title>
    <link rel="stylesheet" href="{{ public_path('css/reporte.css') }}">
    <style>
        .estado-1 { background:#ffc107; color:#000; padding:2px 6px; border-radius:4px; font-size:11px; }
        .estado-2 { background:#28a745; color:#fff; padding:2px 6px; border-radius:4px; font-size:11px; }
        .estado-3 { background:#dc3545; color:#fff; padding:2px 6px; border-radius:4px; font-size:11px; }
    </style>
</head>
<body>
    <div>
        <img src="{{ public_path('img/logo.jpg') }}" alt="Logo">
    </div>
    <h1 class="titulo">Reporte de Seguimientos Médicos</h1>
    <div class="titulo">Fecha: {{ \Carbon\Carbon::now()->timezone('America/Lima')->toDateString() }}</div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Paciente</th>
                <th>Documento</th>
                <th>Doctor</th>
                <th>Fecha Seguimiento</th>
                <th>Motivo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resultado['data'] as $item)
            <tr>
                <td>{{ $item['id'] }}</td>
                <td>{{ $item['patient']['full_name'] ?? '-' }}</td>
                <td>{{ $item['patient']['n_document'] ?? '-' }}</td>
                <td>{{ $item['doctor']['full_name'] ?? '-' }}</td>
                <td>{{ $item['fecha_seguimiento'] }}</td>
                <td>{{ \Illuminate\Support\Str::limit($item['motivo'], 50) }}</td>
                <td>
                    @if($item['estado'] == 1)
                        <span class="estado-1">Pendiente</span>
                    @elseif($item['estado'] == 2)
                        <span class="estado-2">Realizado</span>
                    @else
                        <span class="estado-3">Cancelado</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin-top:16px; font-size:11px; color:#666;">
        Total de registros: {{ count($resultado['data']) }}
    </p>
</body>
</html>
