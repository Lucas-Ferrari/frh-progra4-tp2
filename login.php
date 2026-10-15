<?php

$tipoAcceso = isset($_GET["tipo"]) ? $_GET["tipo"] : "general";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Banco - Login</title>
</head>

<body>

<h2>Ingreso al Banco (<?php echo htmlspecialchars($tipoAcceso); ?>)</h2>

<form action="validar_loguin.php" method="POST">

    <!-- Campo oculto: no lo ve el usuario, pero viaja igual que
         email y password. Así validar_loguin.php sabe por qué
         acceso entró la persona, para poder cruzarlo con su rol real. -->
    <input type="hidden" name="tipo_acceso" value="<?php echo htmlspecialchars($tipoAcceso); ?>">

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>

    <br><br>

    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="password" required>

    <br><br>

    <button type="submit">Ingresar</button>

</form>

</body>
</html>