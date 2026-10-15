<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_CLIENTE]);

$usuario = consultarUno(
    $conexion,
    "SELECT nombre, apellido, dni, fecha_nacimiento, email, fecha_registro FROM usuarios WHERE id_usuario = ?",
    "i",
    [$_SESSION["id_usuario"]]
);

$titulo = "Mis datos";
require_once(__DIR__ . "/../includes/header.php");

?>

<section class="tarjeta formulario formulario-centrado">
    <h1>Mis datos</h1>

    <div class="fila-campos">
        <div class="campo">
            <label for="nombre_completo">Nombre y apellido</label>
            <input type="text" id="nombre_completo" value="<?php echo e($usuario["nombre"] . " " . $usuario["apellido"]); ?>" disabled>
        </div>
        <div class="campo">
            <label for="dni">DNI</label>
            <input type="text" id="dni" value="<?php echo e($usuario["dni"]); ?>" disabled>
        </div>
    </div>
    <div class="fila-campos">
        <div class="campo">
            <label for="fecha_nacimiento">Fecha de nacimiento</label>
            <input type="text" id="fecha_nacimiento" value="<?php echo e(formatoFecha($usuario["fecha_nacimiento"], false)); ?>" disabled>
        </div>
        <div class="campo">
            <label for="fecha_registro">Cliente desde</label>
            <input type="text" id="fecha_registro" value="<?php echo e(formatoFecha($usuario["fecha_registro"], false)); ?>" disabled>
        </div>
    </div>
    <p class="texto-suave">Para modificar nombre, DNI o fecha de nacimiento acercate a una sucursal.</p>

    <h2>Modificar email</h2>
    <form action="../procesos/procesar_mis_datos.php" method="POST" novalidate>
        <div class="campo">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="100" required value="<?php echo e(valorAnterior("email", $usuario["email"])); ?>">
        </div>
        <div class="campo">
            <label for="password_actual">Contraseña actual (para confirmar el cambio)</label>
            <input type="password" id="password_actual" name="password_actual" required autocomplete="current-password">
        </div>
        <div class="acciones">
            <button type="submit" class="boton">Guardar</button>
            <a href="panel.php" class="boton boton-secundario">Volver</a>
        </div>
    </form>
</section>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
