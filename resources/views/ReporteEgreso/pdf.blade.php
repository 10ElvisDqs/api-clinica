<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Egresos</title>
    <link rel="stylesheet" href="{{ public_path('css/reporte.css') }}">
    <style>
        .tipo { background:#17a2b8; color:#fff; padding:2px 6px; border-radius:4px; font-size:11px; }
    </style>
</head>
<body>
    <div>
        <img src="{{ public_path('img/logo.jpg') }}" alt="Logo">
    </div>
    <h1 class="titulo">Reporte de Egresos de Pacientes</h1>
    <div class="titulo">Fecha: {{ \Carbon\Carbon::now()->timezone('America/Lima')->toDateString() }}</div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Paciente</th>
                <th>Documento</th>
                <th>Doctor</th>
                <th>F. Ingreso</th>
                <th>F. Egreso</th>
                <th>Tipo Egreso</th>
                <th>Diagnóstico Final</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resultado['data'] as $item)
            <tr>
                <td>{{ $item['id'] }}</td>
                <td>{{ $item['patient']['full_name'] ?? '-' }}</td>
                <td>{{ $item['patient']['n_document'] ?? '-' }}</td>
                <td>{{ $item['doctor']['full_name'] ?? '-' }}</td>
                <td>{{ $item['ingreso']['fecha_ingreso'] ?? '-' }}</td>
                <td>{{ $item['fecha_egreso'] }}</td>
                <td>
                    <span class="tipo">
                        @switch($item['tipo_egreso'])
                            @case('alta_medica')   Alta Médica @break
                            @case('referido')      Referido    @break
                            @case('voluntario')    Voluntario  @break
                            @case('fallecido')     Fallecido   @break
                            @default {{ $item['tipo_egreso'] }}
                        @endswitch
                    </span>
                </td>
                <td>{{ \Illuminate\Support\Str::limit($item['diagnostico_final'], 50) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin-top:16px; font-size:11px; color:#666;">
        Total de registros: {{ count($resultado['data']) }}
    </p>
</body>
</html>
