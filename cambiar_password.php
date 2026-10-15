<?php

require_once(__DIR__ . "/includes/auth.php");

// Solo pedimos login: esta es justamente la página a la que se
// redirige cuando la contraseña está pendiente de cambio.
requerirLogin();

$obligatorio = !empty($_SESSION["debe_cambiar_password"]);

$titulo = "Cambiar contraseña";
require_once(__DIR__ . "/includes/header.php");

?>

<section class="tarjeta formulario formulario-centrado">
    <h1>Cambiar contraseña</h1>

    <?php if ($obligatorio): ?>
        <div class="mensaje mensaje-info">
            Hola, <?php echo e($_SESSION["nombre"]); ?>. Estás usando una contraseña provisoria:
            tenés que elegir una nueva para continuar.
        </div>
    <?php endif; ?>

    <form action="procesos/procesar_cambio_password.php" method="POST" novalidate>

        <div class="campo">
            <label for="password_actual"><?php echo $obligatorio ? "Contraseña provisoria" : "Contraseña actual"; ?></label>
            <input type="password" id="password_actual" name="password_actual" required autocomplete="current-password">
        </div>

        <div class="campo">
            <label for="password_nueva">Contraseña nueva</label>
            <input type="password" id="password_nueva" name="password_nueva" required autocomplete="new-password" data-password-min="<?php echo PASSWORD_LONGITUD_MINIMA; ?>">
            <span class="ayuda">Mínimo <?php echo PASSWORD_LONGITUD_MINIMA; ?> caracteres.</span>
        </div>

        <div class="campo">
            <label for="password_nueva_repetir">Repetir contraseña nueva</label>
            <input type="password" id="password_nueva_repetir" name="password_nueva_repetir" required autocomplete="new-password" data-igual-a="password_nueva">
        </div>

        <button type="submit" class="boton">Cambiar contraseña</button>
    </form>
</section>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
