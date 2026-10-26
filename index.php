<?php

$titulo = "Inicio";
require_once(__DIR__ . "/includes/header.php");

?>

<section class="portada">
    <div class="portada-texto" data-aparecer>
        <p class="sobretitulo">Caja de ahorro · $ 0 de mantenimiento</p>
        <h1>El banco que no se mete en el medio.</h1>
        <p>
            Abrí tu cuenta en minutos, mirá cada movimiento al instante y transferí
            las 24 horas. Sin comisiones escondidas.
        </p>
        <div class="acciones">
            <?php if (estaLogueado()): ?>
                <a href="<?php echo e(panelSegunRol(rolActual())); ?>" class="boton boton-acento">Ir a mi panel</a>
            <?php else: ?>
                <a href="registro.php" class="boton boton-acento">Abrir mi cuenta</a>
                <a href="login.php" class="boton boton-fantasma">Ya soy cliente</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="vista-previa" aria-hidden="true" data-aparecer style="--retraso: 0.2s">
        <div class="vista-previa-saldo">
            <span>Caja de ahorro en pesos</span>
            <strong>$ <span data-saldo>248.315</span><small>,40</small></strong>
        </div>
        <ul>
            <li><span>Transferencia de Lucía M.<small>Hoy, 09:42</small></span><span class="ingreso">+ $ 35.000,00</span></li>
            <li><span>Extracción en sucursal<small>Ayer, 13:10</small></span><span>− $ 12.000,00</span></li>
            <li><span>Depósito<small>Lun 12</small></span><span class="ingreso">+ $ 80.500,00</span></li>
        </ul>
    </div>
</section>

<ul class="datos-banco" data-aparecer>
    <li><strong>$ 0</strong><span>de mantenimiento en la caja de ahorro</span></li>
    <li><strong data-contar="24" data-sufijo=" h">24 h</strong><span>para transferir, todos los días</span></li>
    <li><strong>2010</strong><span>atendiendo en Av. Corrientes</span></li>
</ul>

<section class="quienes" data-aparecer>
    <h2 class="seccion-titulo">Un banco chico, a propósito.</h2>
    <p>
        Banco Andino nació en 2010 con una idea simple: que manejar tu dinero sea fácil,
        transparente y sin costos ocultos. Acompañamos a personas y pequeñas empresas
        con atención en sucursal y una plataforma online disponible todo el día.
    </p>
</section>

<div class="encabezado-productos" data-aparecer>
    <h2 class="seccion-titulo">Qué podés hacer</h2>
    <p class="seccion-bajada">Todo se gestiona desde tu panel una vez que te registrás.</p>
</div>

<section class="productos">
    <article class="producto" data-aparecer>
        <span class="numero">01</span>
        <h3>Caja de ahorro</h3>
        <p>En pesos y en dólares, sin costo de mantenimiento. Se abre sola al registrarte.</p>
    </article>
    <article class="producto" data-aparecer>
        <span class="numero">02</span>
        <h3>Cuenta corriente</h3>
        <p>Para profesionales y comercios: cobrá, pagá a proveedores y gestioná tus cheques.</p>
    </article>
    <article class="producto" data-aparecer>
        <span class="numero">03</span>
        <h3>Tarjetas</h3>
        <p>Débito incluida con tu cuenta y crédito con cuotas sin interés en comercios adheridos.</p>
    </article>
    <article class="producto" data-aparecer>
        <span class="numero">04</span>
        <h3>Transferencias</h3>
        <p>Al instante entre tus cuentas o a cualquier cuenta del banco, todos los días.</p>
    </article>
</section>

<section class="franja" data-aparecer>
    <div>
        <h2>¿Tenés una duda?</h2>
        <p>Te respondemos por email en el día hábil, o acercate a la sucursal de 10 a 15 h.</p>
    </div>
    <a href="contacto.php" class="boton boton-acento">Escribinos</a>
</section>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
