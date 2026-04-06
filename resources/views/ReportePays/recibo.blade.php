<!DOCTYPE html>
<html>
<head>
    <title>Recibo</title>
    <link rel="stylesheet" href="{{ public_path('css/recibo.css') }}">
</head>
<body>
    <div class="header">
        <img class="logo" src="{{ public_path('img/logo.jpg') }}" alt="Logo">
        <img class="background-img" src="{{ public_path('img/login-02.jpg') }}" alt="Background">
    </div>

    <div class="content">
        <h1>Recibo de Pago</h1>
        <p>Detalles del appointment: # {{ $id }} </p>
        <!-- Renderiza los datos del appointment aquí -->
        <p>Doctor: {{ $doctor['full_name'] }}</p>
        <p>Especialidad: {{ $specialitie['name'] }}</p>
        <p>Paciente: {{ $patient['full_name'] }}</p>
        <p>Fecha: {{ $date_appointment_format }}</p>
        <p>Costo de la Cita: {{ $amount }}</p>
        <p>Monto Pendiente: {{$pending_amount}}</p>

        @if ($status_pay == 2 )
            <p>Estado: Deuda</p>
        @else
            @if ($status_pay == 1)
                <p>Estado: Pagado</p>
            @endif
        @endif

        <h2>Pagos</h2>
        <table class="payments-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Appointment ID</th>
                    <th>Monto</th>
                    <th>Método de Pago</th>
                    <th>Fecha de Creación</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $payment)
                <tr>
                    <td>{{ $payment['id'] }}</td>
                    <td>{{ $payment['appointment_id'] }}</td>
                    <td>{{ $payment['amount'] }}</td>
                    <td>{{ $payment['method_payment'] }}</td>
                    <td>{{ $payment['created_at'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
