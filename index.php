<?php

$titulo = "Inicio";
require_once(__DIR__ . "/includes/header.php");

?>

<section class="portada">
    <div class="portada-texto">
        <h1>Tu plata, <em>bien tejida.</em></h1>
        <p class="portada-bajada">
            Abrís tu cuenta en minutos, ves cada peso que entra y sale,
            y transferís a cualquier hora. Sin vueltas y sin letra chica.
        </p>
        <div class="acciones">
            <?php if (estaLogueado()): ?>
                <a href="<?php echo e(panelSegunRol(rolActual())); ?>" class="boton">Ir a mi panel</a>
            <?php else: ?>
                <a href="registro.php" class="boton boton-secundario">Abrí tu cuenta gratis</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="portada-tela">
        <?php if (estaLogueado()): ?>
            <div class="panel-ingreso">
                <h2>Hola, <?php echo e($_SESSION["nombre"]); ?></h2>
                <p>Tus cuentas te esperan en el panel.</p>
                <a href="<?php echo e(panelSegunRol(rolActual())); ?>" class="boton">Ir a mi panel</a>
            </div>
        <?php else: ?>
            <form class="panel-ingreso" action="procesos/validar_login.php" method="POST" novalidate>
                <h2>Ingresá a tu banco</h2>

                <div class="campo">
                    <label>Tipo de acceso</label>
                    <div class="opciones-radio">
                        <label>
                            <input type="radio" name="tipo_acceso" value="cliente" checked>
                            Clientes
                        </label>
                        <label>
                            <input type="radio" name="tipo_acceso" value="empleado">
                            Empleados
                        </label>
                    </div>
                </div>

                <div class="campo">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autocomplete="username">
                </div>

                <div class="campo">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit" class="boton">Ingresar</button>
                <p class="texto-suave">¿Primera vez? <a href="registro.php">Creá tu usuario</a></p>
            </form>
        <?php endif; ?>
    </div>
</section>

<h2>Lo que tejemos con vos</h2>

<ul class="muestrario">
    <li>
        <span class="muestra" style="--a: var(--cochinilla); --b: var(--mostaza); --c: var(--lana-clara);" aria-hidden="true"></span>
        <h3>Caja de ahorro</h3>
        <p>En pesos y en dólares, sin costo de mantenimiento. Se abre sola el día que te registrás.</p>
    </li>
    <li>
        <span class="muestra" style="--a: var(--indigo); --b: var(--verde); --c: var(--mostaza);" aria-hidden="true"></span>
        <h3>Cuenta corriente</h3>
        <p>Para profesionales y comercios: cobrá, pagá a proveedores y llevá tus cheques en un solo lugar.</p>
    </li>
    <li>
        <span class="muestra" style="--a: var(--verde); --b: var(--rosa); --c: var(--lana-clara);" aria-hidden="true"></span>
        <h3>Tarjetas</h3>
        <p>La débito viene con tu cuenta. La de crédito, con cuotas sin interés en comercios adheridos.</p>
    </li>
    <li>
        <span class="muestra" style="--a: var(--mostaza); --b: var(--cochinilla); --c: var(--indigo);" aria-hidden="true"></span>
        <h3>Transferencias</h3>
        <p>Al instante entre tus cuentas o a cualquier cuenta del banco, las 24 horas, los 365 días.</p>
    </li>
</ul>

<div class="escalonado" aria-hidden="true"></div>

<section class="historia">
    <h2>Un banco del norte, hecho para todo el país</h2>
    <div>
        <p>
            Banco Andino nació en 2010 en Jujuy con una idea simple: que manejar tu dinero
            sea tan claro como una buena trama. Cada movimiento a la vista, cada costo dicho
            de antemano.
        </p>
        <p>
            Hoy acompañamos a miles de personas y pequeñas empresas con una sucursal
            donde te atienden por tu nombre y una plataforma online que no cierra nunca.
        </p>
        <ol class="pasos">
            <li><b>1</b><span>Te registrás con tu DNI y tu email.</span></li>
            <li><b>2</b><span>Tu caja de ahorro en pesos queda abierta al instante.</span></li>
            <li><b>3</b><span>Ingresás, consultás saldos y transferís cuando quieras.</span></li>
        </ol>
    </div>
</section>

<div class="escalonado" aria-hidden="true"></div>

<section class="cierre">
    <div>
        <h2>¿Te quedó alguna duda?</h2>
        <p>Escribinos y te respondemos en el día hábil, o pasá por la sucursal.</p>
    </div>
    <a href="contacto.php" class="boton boton-secundario">Escribinos</a>
</section>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
