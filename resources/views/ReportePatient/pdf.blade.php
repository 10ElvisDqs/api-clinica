<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="{{public_path('css/reporte.css')}}">

</head>
<body>
    <div class="titulo">
        {{ \Carbon\Carbon::now()->toDateString() }}
    </div>

    <div>

        <img src="{{public_path('img/logo.jpg')}}" alt="">
        <img class="background-img" src="{{ public_path('img/login-02.jpg') }}" alt="Background">
        {{-- <img src="{{ asset('build/assets/img/logo.png') }}" alt="Texto alternativo del logo" --}}
    </div>
    <h1 class="titulo"> Reporte de Pacientes</h1>

    <table >
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre y Apellido</th>
                <th>Nro Documento</th>
                <th>Telefono</th>
                <th>Email</th>
                <th>Sexo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resultado['data'] as $user)
                <tr>
                    <td>{{ $user['id'] }}</td>
                    <td>{{ $user['full_name'] }}</td>
                    <td>{{ $user['n_document'] }}</td>
                    <td>{{ $user['mobile'] }}</td>
                    <td>{{ $user['email'] }}</td>
                    <td>{{ $user['gender'] == 1 ? 'Masculino' : 'Femenino' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
