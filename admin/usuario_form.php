<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN]);

// Con ?id=X es una modificación; sin id, un alta.
$idUsuario = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$usuario = null;

if ($idUsuario > 0) {
    $usuario = consultarUno($conexion, "SELECT * FROM usuarios WHERE id_usuario = ?", "i", [$idUsuario]);
    if ($usuario === null) {
        redirigir("admin/usuarios.php?error=no_encontrado");
    }
}

$esAlta = $usuario === null;
$esYo = !$esAlta && (int) $usuario["id_usuario"] === (int) $_SESSION["id_usuario"];

// Valores del formulario: lo que se cargó antes de un error, o los datos guardados.
function valorCampo($nombre)
{
    global $usuario;
    return valorAnterior($nombre, $usuario !== null ? $usuario[$nombre] : "");
}

$rolSeleccionado = (int) valorCampo("id_rol");
if ($rolSeleccionado === 0) {
    $rolSeleccionado = ROL_CLIENTE;
}

$titulo = $esAlta ? "Nuevo usuario" : "Editar usuario";
require_once(__DIR__ . "/../includes/header.php");

?>

<section class="tarjeta formulario formulario-centrado">
    <h1><?php echo e($titulo); ?></h1>

    <?php if ($esAlta): ?>
        <p class="texto-suave">Se generará una contraseña provisoria que el usuario deberá cambiar en su primer ingreso.</p>
    <?php endif; ?>

    <form action="../procesos/procesar_usuario.php" method="POST" novalidate>
        <input type="hidden" name="accion" value="<?php echo $esAlta ? "alta" : "modificar"; ?>">
        <?php if (!$esAlta): ?>
            <input type="hidden" name="id_usuario" value="<?php echo (int) $usuario["id_usuario"]; ?>">
        <?php endif; ?>

        <div class="fila-campos">
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" autocomplete="off" maxlength="50" required value="<?php echo e(valorCampo("nombre")); ?>">
            </div>
            <div class="campo">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" autocomplete="off" maxlength="50" required value="<?php echo e(valorCampo("apellido")); ?>">
            </div>
        </div>

        <div class="fila-campos">
            <div class="campo">
                <label for="dni">DNI</label>
                <input type="text" id="dni" name="dni" autocomplete="off" inputmode="numeric" maxlength="8" required data-dni value="<?php echo e(valorCampo("dni")); ?>">
            </div>
            <div class="campo">
                <label for="fecha_nacimiento">Fecha de nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" max="<?php echo date("Y-m-d"); ?>" required value="<?php echo e(valorCampo("fecha_nacimiento")); ?>">
            </div>
        </div>

        <div class="campo">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" autocomplete="off" spellcheck="false" maxlength="100" required value="<?php echo e(valorCampo("email")); ?>">
        </div>

        <div class="campo">
            <label for="id_rol">Rol</label>
            <select id="id_rol" name="id_rol" <?php echo $esYo ? "disabled" : ""; ?>>
                <?php foreach ([ROL_CLIENTE, ROL_OPERADOR, ROL_ADMIN] as $rol): ?>
                    <option value="<?php echo $rol; ?>" <?php echo $rolSeleccionado === $rol ? "selected" : ""; ?>><?php echo e(nombreRol($rol)); ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($esYo): ?>
                <span class="ayuda">No podés cambiar tu propio rol.</span>
            <?php endif; ?>
        </div>

        <div class="acciones">
            <button type="submit" class="boton"><?php echo $esAlta ? "Crear usuario" : "Guardar cambios"; ?></button>
            <a href="usuarios.php" class="boton boton-secundario">Cancelar</a>
        </div>
    </form>
</section>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
