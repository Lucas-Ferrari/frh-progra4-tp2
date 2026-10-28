<?php

require_once(__DIR__ . "/../includes/auth.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN, ROL_OPERADOR]);

$titulo = "Alta de cliente";
require_once(__DIR__ . "/../includes/header.php");

?>

<section class="tarjeta formulario formulario-centrado">
    <h1>Alta de cliente</h1>
    <p class="texto-suave">
        Se le abrirá una caja de ahorro en pesos y se generará una contraseña provisoria
        que el cliente deberá cambiar en su primer ingreso.
    </p>

    <form action="../procesos/procesar_cliente.php" method="POST" novalidate>

        <div class="fila-campos">
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" autocomplete="off" maxlength="50" required value="<?php echo e(valorAnterior("nombre")); ?>">
            </div>
            <div class="campo">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" autocomplete="off" maxlength="50" required value="<?php echo e(valorAnterior("apellido")); ?>">
            </div>
        </div>

        <div class="fila-campos">
            <div class="campo">
                <label for="dni">DNI</label>
                <input type="text" id="dni" name="dni" autocomplete="off" inputmode="numeric" maxlength="8" required data-dni value="<?php echo e(valorAnterior("dni")); ?>">
            </div>
            <div class="campo">
                <label for="fecha_nacimiento">Fecha de nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" max="<?php echo date("Y-m-d"); ?>" required value="<?php echo e(valorAnterior("fecha_nacimiento")); ?>">
            </div>
        </div>

        <div class="campo">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" autocomplete="off" spellcheck="false" maxlength="100" required value="<?php echo e(valorAnterior("email")); ?>">
        </div>

        <div class="acciones">
            <button type="submit" class="boton">Cargar cliente</button>
            <a href="<?php echo RAIZ . panelSegunRol(rolActual()); ?>" class="boton boton-secundario">Volver</a>
        </div>
    </form>
</section>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
