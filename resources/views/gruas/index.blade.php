<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Grúas</title>
</head>
<body>
    <h1>Listado de Grúas</h1>
    <ul>
        @foreach ($gruas as $grua)
            <li>{{ $grua->nombre }} - {{ $grua->tipo }} - {{ $grua->capacidad }}T</li>
        @endforeach
    </ul>
</body>
</html>
