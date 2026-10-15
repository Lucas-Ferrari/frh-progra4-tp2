<?php

// Toda página "protegida" (a la que solo debería entrar alguien logueado)
// necesita session_start() para poder leer los datos de la sesión.
session_start();

// Guard clause: si no hay sesión activa, no debería poder ver este panel.
if (!isset($_SESSION["id_usuario"])) {
    die("No hay ninguna sesión activa. Iniciá sesión primero.");
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Banco - Panel Administrador</title>
</head>
<body>

<h2>Panel del Administrador</h2>
<p>Hola, <?php echo $_SESSION["nombre"]; ?>. Bienvenido a tu panel.</p>

</body>
</html>