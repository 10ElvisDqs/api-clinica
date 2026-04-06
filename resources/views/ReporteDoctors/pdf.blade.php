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
    <h1 class="titulo"> Reporte de Doctores</h1>

    <table >
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Email</th>
                <th>Especialidad</th>
                <th>Sexo</th>
                {{-- <th>Sexo</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($resultado['data'] as $user)
                <tr>
                    <td>{{ $user['id'] }}</td>
                    <td>{{ $user['name']  }}</td>
                    <td>{{ $user['surname']  }}</td>
                    <td>{{ $user['email'] }}</td>
                    <td>{{ $user['specialitie']['name'] }}</td>
                    <td>{{ $user['gender'] == 1 ? 'Masculino' : 'Femenino' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
