<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Banco - Alta de Cliente</title>
</head>

<body>

<h2>Alta de Cliente Nuevo</h2>

<form action="../procesos/procesar_cliente.php" method="POST">

    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" required>

    <br><br>

    <label for="apellido">Apellido:</label>
    <input type="text" id="apellido" name="apellido" required>

    <br><br>

    <label for="dni">DNI:</label>
    <input type="text" id="dni" name="dni" required>

    <br><br>

    <label for="fecha_nacimiento">Fecha de nacimiento:</label>
    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>

    <br><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>

    <br><br>

    <button type="submit">Cargar Cliente</button>

</form>

</body>
</html>