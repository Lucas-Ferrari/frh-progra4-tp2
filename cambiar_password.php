<?php

// Necesitamos saber quién sos para mostrarle el formulario a la persona correcta.
session_start();

// Guard clause: si no hay sesión activa, no tiene sentido mostrar este formulario.
if (!isset($_SESSION["id_usuario"])) {
    die("No hay ninguna sesión activa. Iniciá sesión primero.");
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Banco - Cambiar Contraseña</title>
</head>

<body>

<h2>Cambiar Contraseña</h2>

<!-- Saludo simple usando el nombre guardado en sesión, para confirmarle
     al usuario que está cambiando SU propia contraseña. -->
<p>Hola, <?php echo $_SESSION["nombre"]; ?></p>

<form action="procesos/procesar_cambio_password.php" method="POST">

    <label for="password_actual">Contraseña actual:</label>
    <input type="password" id="password_actual" name="password_actual" required>

    <br><br>

    <label for="password_nueva">Contraseña nueva:</label>
    <input type="password" id="password_nueva" name="password_nueva" required>

    <br><br>

    <label for="password_nueva_repetir">Repetir contraseña nueva:</label>
    <input type="password" id="password_nueva_repetir" name="password_nueva_repetir" required>

    <br><br>

    <button type="submit">Cambiar Contraseña</button>

</form>

</body>
</html>