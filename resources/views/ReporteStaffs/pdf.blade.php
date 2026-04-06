<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Listado de Staffs</title>
    <link rel="stylesheet" href="{{public_path('css/reporte.css')}}">
</head>
<body>
    <div id="header">
        <img class="imgHeader" src="{{public_path('img/logo.jpg')}}" alt=""><br>
        <img class="background-img" src="{{ public_path('img/login-02.jpg') }}" alt="Background">
        <div class="infoHeader">
            <div class="titulo">
            </div>
        </div>
    </div>
    <div class="texto-centrado">
        <h1 class="titulo"> Reporte de Staffs </h1>
    </div>
    <table class="texto-centrado">
        <thead>
            <tr>
                {{-- <th class="texto-centrado">ID</th> --}}
                <th class="texto-centrado">Nombre</th>
                <th class="texto-centrado">Apellido</th>
                <th class="texto-centrado">Email</th>
                <th class="texto-centrado">Género</th>
                <th class="texto-centrado">Address</th>
                <th class="texto-centrado">Rol</th>
                <th class="texto-centrado">Mobile</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultado['data'] as $user)
                <tr>
                    {{-- <td>{{ $user['id'] }}</td> --}}
                    <td>{{ $user['name'] }}</td>
                    <td>{{ $user['surname']}}</td>
                    <td>{{ $user['email'] }}</td>
                    <td>{{ $user['gender'] == 1 ? 'Masculino' : 'Femenino' }}</td>
                    <td>{{ $user['address'] }}</td>
                    <td>{{ $user['role']['name'] }}</td>
                    <td>{{ $user['mobile'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
