<?php

require_once(__DIR__ . "/includes/funciones.php");

// Si ya está logueado, no tiene sentido registrarse de nuevo.
if (estaLogueado()) {
    redirigir(panelSegunRol(rolActual()));
}

$titulo = "Registrarse";
require_once(__DIR__ . "/includes/header.php");

?>

<section class="tarjeta formulario formulario-centrado">
    <h1>Crear cuenta</h1>
    <p class="texto-suave">Registrate como cliente y te abrimos una caja de ahorro en pesos sin costo.</p>

    <form action="procesos/procesar_registro.php" method="POST" novalidate>

        <div class="fila-campos">
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" maxlength="50" required value="<?php echo e(valorAnterior("nombre")); ?>">
            </div>
            <div class="campo">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" maxlength="50" required value="<?php echo e(valorAnterior("apellido")); ?>">
            </div>
        </div>

        <div class="fila-campos">
            <div class="campo">
                <label for="dni">DNI</label>
                <input type="text" id="dni" name="dni" inputmode="numeric" maxlength="8" required data-dni value="<?php echo e(valorAnterior("dni")); ?>">
                <span class="ayuda">Sin puntos, 7 u 8 dígitos.</span>
            </div>
            <div class="campo">
                <label for="fecha_nacimiento">Fecha de nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" max="<?php echo date("Y-m-d"); ?>" required value="<?php echo e(valorAnterior("fecha_nacimiento")); ?>">
            </div>
        </div>

        <div class="campo">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="100" required value="<?php echo e(valorAnterior("email")); ?>">
        </div>

        <div class="fila-campos">
            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required data-password-min="<?php echo PASSWORD_LONGITUD_MINIMA; ?>">
                <span class="ayuda">Mínimo <?php echo PASSWORD_LONGITUD_MINIMA; ?> caracteres.</span>
            </div>
            <div class="campo">
                <label for="password_repetir">Repetir contraseña</label>
                <input type="password" id="password_repetir" name="password_repetir" required data-igual-a="password">
            </div>
        </div>

        <button type="submit" class="boton">Registrarme</button>
        <p class="texto-suave">¿Ya tenés cuenta? <a href="login.php">Ingresá</a></p>
    </form>
</section>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
