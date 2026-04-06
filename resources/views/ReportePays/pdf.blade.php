<!DOCTYPE html>
<html>
<head>

    <title>Listado de Citas</title>
    <link rel="stylesheet" href="{{public_path('css/reporte.css')}}">
</head>
<body>
    <div id="header">
        <img class="imgHeader" src="{{public_path('img/logo.jpg')}}" alt=""><br>
        <div class="infoHeader">
            <div class="titulo">
            </div>
        </div>
    </div>
    {{-- {{ \Carbon\Carbon::now()->toDateString() }} --}}
    <div class="texto-centrado">
        <h1 class="titulo">Reporte de Pagos</h1>
    </div>

    {{-- @if(!empty($resultado['appointments']['data'])) --}}
        <table class="texto-centrado">
            <thead>
                <tr>
                    <th class="texto-centrado">ID</th>
                    <th class="texto-centrado">Doctor</th>
                    <th class="texto-centrado">Paciente</th>
                    <th class="texto-centrado">Fecha de Cita</th>
                    <th class="texto-centrado">Especialidad</th>
                    <th class="texto-centrado">Horario</th>
                    <th class="texto-centrado">Pagos</th>
                    <th class="texto-centrado">Estado del Pago</th>
                    <th class="texto-centrado">Costo de La Cita</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultado['data'] as $appointment)
                    <tr>
                        <td>{{ $appointment['id'] }}</td>
                        <td>{{ $appointment['doctor']['full_name'] }}</td>
                        <td>{{ $appointment['patient']['full_name'] }}</td>
                        <td>{{ $appointment['date_appointment_format'] }}</td>
                        <td>{{ $appointment['specialitie']['name'] }}</td>
                        <td>{{ $appointment['segment_hour']['format_segment']['format_hour_start'] }} - {{ $appointment['segment_hour']['format_segment']['format_hour_end'] }}</td>
                        <td>

                            @foreach($appointment['payments'] as $payment)
                                <span>
                                    <p>ID: {{ $payment['id'] }}, Monto: {{ $payment['amount'] }}</p>
                                    <p> Método: {{ $payment['method_payment'] }}</p>
                                    <p> Fecha: {{ $payment['created_at'] }}</p>
                                </span>
                                <hr>

                            @endforeach
                        </td>

                        <td class="texto-centrado">
                            @if ($appointment['status_pay']== 2)
                                <span class="badge rounded-pill text-bg-ranger">Deuda</span>
                            @elseif ($appointment['status_pay'] == 1)
                                <span class="badge rounded-pill text-bg-success">Pagado</span>
                            @else
                                Desconocido
                            @endif
                        </td>
                        <td class="texto-centrado">{{ $appointment['amount'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    {{-- @else
        <p>No hay citas disponibles.</p>
    @endif --}}
</body>
</html>
