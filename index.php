<?php

$titulo = "Inicio";
require_once(__DIR__ . "/includes/header.php");

?>

<section class="portada">
    <h1>Banco Andino</h1>
    <p>
        Tu banco digital: abrí tu cuenta en minutos, consultá tus saldos y movimientos
        y transferí dinero las 24 horas, desde cualquier dispositivo.
    </p>
    <div class="acciones">
        <?php if (estaLogueado()): ?>
            <a href="<?php echo e(panelSegunRol(rolActual())); ?>" class="boton">Ir a mi panel</a>
        <?php else: ?>
            <a href="registro.php" class="boton">Registrarse</a>
            <a href="login.php" class="boton boton-secundario">Ingresar</a>
        <?php endif; ?>
    </div>
</section>

<section class="tarjeta">
    <h2>Quiénes somos</h2>
    <p>
        Banco Andino nació en 2010 con una idea simple: que manejar tu dinero sea fácil,
        transparente y sin costos ocultos. Hoy acompañamos a miles de personas y
        pequeñas empresas con atención personalizada en nuestra sucursal y una plataforma
        online disponible todo el día.
    </p>
</section>

<h2>Nuestros productos</h2>

<section class="grilla">
    <article class="tarjeta producto">
        <div class="icono" aria-hidden="true">🏦</div>
        <h3>Caja de ahorro</h3>
        <p>En pesos y en dólares, sin costo de mantenimiento. Se abre automáticamente al registrarte.</p>
    </article>
    <article class="tarjeta producto">
        <div class="icono" aria-hidden="true">📈</div>
        <h3>Cuenta corriente</h3>
        <p>Ideal para profesionales y comercios: cobrá, pagá a proveedores y gestioná tus cheques.</p>
    </article>
    <article class="tarjeta producto">
        <div class="icono" aria-hidden="true">💳</div>
        <h3>Tarjetas</h3>
        <p>Débito incluida con tu cuenta y tarjetas de crédito con cuotas sin interés en comercios adheridos.</p>
    </article>
    <article class="tarjeta producto">
        <div class="icono" aria-hidden="true">🔁</div>
        <h3>Transferencias</h3>
        <p>Transferí al instante entre tus cuentas o a cualquier cuenta del banco, las 24 horas.</p>
    </article>
</section>

<section class="tarjeta">
    <h2>¿Tenés dudas?</h2>
    <p>Escribinos desde el <a href="contacto.php">formulario de contacto</a> o acercate a nuestra sucursal.</p>
</section>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
