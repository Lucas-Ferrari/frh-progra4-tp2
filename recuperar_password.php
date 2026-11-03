<?php

require_once(__DIR__ . "/includes/funciones.php");

if (estaLogueado()) {
    redirigir(panelSegunRol(rolActual()));
}

// "Empezar de nuevo" descarta el pedido en curso.
if (isset($_GET["reiniciar"])) {
    unset($_SESSION["recuperacion"]);
}

$rec = isset($_SESSION["recuperacion"]) && time() <= $_SESSION["recuperacion"]["expira"] ? $_SESSION["recuperacion"] : null;
$flash = isset($_SESSION["flash_codigo"]) ? $_SESSION["flash_codigo"] : null;
unset($_SESSION["flash_codigo"]);

$titulo = "Recuperar contraseña";
require_once(__DIR__ . "/includes/header.php");

?>

<?php if ($flash !== null) : ?>
    <script>console.log("Recovery code sent is " + <?php echo json_encode($flash["codigo"]); ?>);</script>
    <?php if (!$flash["mail_enviado"]) : ?>
        <div class="mensaje mensaje-info">No se pudo enviar el mail desde este servidor. Código de recuperación (debug): <code class="password-provisoria"><?php echo e($flash["codigo"]); ?></code></div>
    <?php endif; ?>
<?php endif; ?>

<section class="tarjeta formulario formulario-centrado">
    <h1>Recuperar contraseña</h1>

    <?php if ($rec === null) : ?>
        <p class="texto-suave">Ingresá tu email y te enviamos un código para crear una contraseña nueva.</p>
        <form action="procesos/procesar_recuperar_password.php" method="POST" novalidate>
            <input type="hidden" name="paso" value="email">
            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" autocomplete="email" spellcheck="false" maxlength="100" required value="<?php echo e(valorAnterior("email")); ?>">
            </div>
            <button type="submit" class="boton">Enviar código</button>
        </form>

    <?php elseif (!$rec["verificado"]) : ?>
        <p class="texto-suave">Si <strong><?php echo e($rec["email"]); ?></strong> está registrado, te enviamos un código de 6 dígitos.</p>
        <form action="procesos/procesar_recuperar_password.php" method="POST" novalidate>
            <input type="hidden" name="paso" value="codigo">
            <div class="campo">
                <label for="codigo">Código</label>
                <input type="text" id="codigo" name="codigo" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required>
            </div>
            <button type="submit" class="boton">Validar código</button>
        </form>

    <?php else : ?>
        <p class="texto-suave">Código validado. Elegí tu contraseña nueva.</p>
        <form action="procesos/procesar_recuperar_password.php" method="POST" novalidate>
            <input type="hidden" name="paso" value="password">
            <div class="fila-campos">
                <div class="campo">
                    <label for="password">Contraseña nueva</label>
                    <input type="password" id="password" name="password" autocomplete="new-password" required data-password-min="<?php echo PASSWORD_LONGITUD_MINIMA; ?>">
                    <span class="ayuda">Mínimo <?php echo PASSWORD_LONGITUD_MINIMA; ?> caracteres.</span>
                </div>
                <div class="campo">
                    <label for="password_repetir">Repetir contraseña</label>
                    <input type="password" id="password_repetir" name="password_repetir" autocomplete="new-password" required data-igual-a="password">
                </div>
            </div>
            <button type="submit" class="boton">Guardar contraseña</button>
        </form>
    <?php endif; ?>

    <p class="texto-suave"><a href="login.php">Volver a ingresar</a><?php if ($rec !== null) : ?> · <a href="recuperar_password.php?reiniciar=1">Empezar de nuevo</a><?php endif; ?></p>
</section>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
