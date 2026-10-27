<?php

$titulo = "Inicio";
require_once(__DIR__ . "/includes/header.php");

?>

<section class="portada">
    <div>
        <h1>Tu plata, simple y a mano.</h1>
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
    </div>

    <!-- Ilustración del producto: datos de ejemplo, no son cuentas reales -->
    <div class="portada-visual" aria-hidden="true">
        <div class="muestra-tarjeta">
            <div class="marca-chip"></div>
            <div class="numero">•••• •••• •••• 2264</div>
            <div class="pie-tarjeta"><span>Banco Andino</span><span>Débito</span></div>
        </div>
        <div class="muestra-saldo">
            <span class="etiqueta">Caja de ahorro en pesos</span>
            <span class="valor">$ 245.380,50</span>
            <div class="movimiento"><span>Transferencia recibida</span><span class="positivo">+ $ 18.000,00</span></div>
        </div>
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

<h2 class="seccion-titulo">Nuestros productos</h2>

<section class="grilla">
    <article class="tarjeta producto">
        <div class="icono" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/><path d="M21 9h-6a2 2 0 0 0 0 6h6z"/></svg>
        </div>
        <h3>Caja de ahorro</h3>
        <p>En pesos y en dólares, sin costo de mantenimiento. Se abre automáticamente al registrarte.</p>
    </article>
    <article class="tarjeta producto">
        <div class="icono" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 6-6"/></svg>
        </div>
        <h3>Cuenta corriente</h3>
        <p>Ideal para profesionales y comercios: cobrá, pagá a proveedores y gestioná tus cheques.</p>
    </article>
    <article class="tarjeta producto">
        <div class="icono" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20"/><path d="M6 15h4"/></svg>
        </div>
        <h3>Tarjetas</h3>
        <p>Débito incluida con tu cuenta y tarjetas de crédito con cuotas sin interés en comercios adheridos.</p>
    </article>
    <article class="tarjeta producto">
        <div class="icono" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3l4 4-4 4"/><path d="M21 7H7"/><path d="M7 21l-4-4 4-4"/><path d="M3 17h14"/></svg>
        </div>
        <h3>Transferencias</h3>
        <p>Transferí al instante entre tus cuentas o a cualquier cuenta del banco, las 24 horas.</p>
    </article>
</section>

<section class="banda-cta">
    <div>
        <h2>¿Tenés dudas?</h2>
        <p>Escribinos desde el formulario de contacto o acercate a nuestra sucursal.</p>
    </div>
    <a href="contacto.php" class="boton">Ir al formulario de contacto</a>
</section>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
