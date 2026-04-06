<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte de Roles y Permisos</title>
    <link rel="stylesheet" href="{{ public_path('css/reporte.css') }}">
</head>
<body>
    <div id="header">
        <img class="imgHeader" src="{{ public_path('img/logo.jpg') }}" alt=""><br>
        <div class="infoHeader">
            <div class="titulo"></div>
        </div>
    </div>
    <div class="texto-centrado">
        <h1 class="titulo">Reporte de Roles y Permisos</h1>
    </div>
    <table class="texto-centrado">
        <thead>
            <tr>
                <th class="texto-centrado">ID</th>
                <th class="texto-centrado">Nombre</th>
                <th class="texto-centrado">Permisos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultado as $rol)
                <tr>
                    <td class="texto-centrado">{{ $rol['id'] }}</td>
                    <td class="texto-centrado">{{ $rol['name'] }}</td>
                    <td class="texto-centrado">
                        @if (count($rol['permission']) > 0)
                            <ul>
                                @foreach($rol['permission'] as $permission)
                                    <li>{{ $permission["name"] }}</li>
                                @endforeach
                            </ul>
                        @else
                            <h3>TODOS LOS PERMISOS</h3>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
