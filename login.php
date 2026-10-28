<?php

require_once(__DIR__ . "/includes/funciones.php");

// Si ya hay sesión, va directo a su panel.
if (estaLogueado()) {
    redirigir(panelSegunRol(rolActual()));
}

// Tipo de acceso elegido: los clientes entran por "cliente" y los
// empleados (administradores y operadores) por "empleado".
$tipoAcceso = valorAnterior("tipo_acceso", isset($_GET["tipo"]) ? $_GET["tipo"] : "cliente");
if ($tipoAcceso !== "empleado") {
    $tipoAcceso = "cliente";
}

$titulo = "Ingresar";
require_once(__DIR__ . "/includes/header.php");

?>

<section class="tarjeta formulario formulario-centrado">
    <h1>Ingresar</h1>

    <form action="procesos/validar_login.php" method="POST" novalidate>

        <!-- fieldset + legend: agrupa los radios y los anuncia juntos en lectores de pantalla -->
        <fieldset class="campo">
            <legend>Tipo de acceso</legend>
            <div class="opciones-radio">
                <label>
                    <input type="radio" name="tipo_acceso" value="cliente" <?php echo $tipoAcceso === "cliente" ? "checked" : ""; ?>>
                    Clientes
                </label>
                <label>
                    <input type="radio" name="tipo_acceso" value="empleado" <?php echo $tipoAcceso === "empleado" ? "checked" : ""; ?>>
                    Empleados
                </label>
            </div>
        </fieldset>

        <div class="campo">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autocomplete="username" value="<?php echo e(valorAnterior("email")); ?>">
        </div>

        <div class="campo">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <a href="recuperar_password.php" class="ayuda">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="boton">Ingresar</button>
        <p class="texto-suave">¿No tenés cuenta? <a href="registro.php">Registrate</a></p>
    </form>
</section>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
