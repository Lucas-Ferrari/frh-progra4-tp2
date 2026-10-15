<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Banco - Bienvenido</title>
</head>

<body>

<h1>Bienvenido al Banco</h1>
<p>Seleccioná tu tipo de acceso para continuar:</p>

<!-- Administrador y Operador comparten el mismo acceso ("empleado"),
     porque ambos son roles internos del banco. Cliente tiene su
     propio acceso separado. -->
<ul>
    <li><a href="login.php?tipo=empleado">Acceso Empleados</a></li>
    <li><a href="login.php?tipo=cliente">Acceso Clientes</a></li>
</ul>

</body>
</html>