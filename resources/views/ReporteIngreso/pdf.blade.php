<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ingresos</title>
    <link rel="stylesheet" href="{{ public_path('css/reporte.css') }}">
    <style>
        .badge-activo  { background:#28a745; color:#fff; padding:2px 8px; border-radius:4px; font-size:11px; }
        .badge-egreso  { background:#6c757d; color:#fff; padding:2px 8px; border-radius:4px; font-size:11px; }
    </style>
</head>
<body>
    <div>
        <img src="{{ public_path('img/logo.jpg') }}" alt="Logo">
    </div>
    <h1 class="titulo">Reporte de Ingresos de Pacientes</h1>
    <div class="titulo">Fecha: {{ \Carbon\Carbon::now()->timezone('America/Lima')->toDateString() }}</div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Paciente</th>
                <th>Documento</th>
                <th>Doctor</th>
                <th>Fecha Ingreso</th>
                <th>Sala / Cama</th>
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
                <td>{{ $item['fecha_ingreso'] }}</td>
                <td>{{ $item['sala'] ?? '-' }} / {{ $item['cama'] ?? '-' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($item['motivo'], 40) }}</td>
                <td>
                    @if($item['estado'] == 1)
                        <span class="badge-activo">Activo</span>
                    @else
                        <span class="badge-egreso">Egresado</span>
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
