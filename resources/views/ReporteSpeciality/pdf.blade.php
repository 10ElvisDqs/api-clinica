<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Listado de Specialities</title>
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
    <div class="texto-centrado">
        <h1 class="titulo"> Reporte de Specialities </h1>
    </div>
    <table class="texto-centrado">
        <thead>
            <tr>
                <th class="texto-centrado">ID</th>
                <th class="texto-centrado">Nombre</th>
                <th class="texto-centrado">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultado as $user)
                <tr>
                    <td class="texto-centrado">{{ $user['id'] }}</td>
                    <td class="texto-centrado">{{ $user['name'] }}</td>
                    <td class="texto-centrado">
                        @if ($user['state'] == 2)
                            <span class="badge rounded-pill text-bg-ranger">Inactivo</span>
                        @elseif ($user['state'] == 1)
                            <span class="badge rounded-pill text-bg-success">Activo</span>
                        @else
                            Desconocido
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
