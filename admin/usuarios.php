<?php

// Listado y buscador de usuarios.
// Administradores: ven todos los usuarios y tienen el ABM completo.
// Operadores: solo buscan clientes (para atenderlos y operar sus cuentas).

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN, ROL_OPERADOR]);

$esAdmin = rolActual() === ROL_ADMIN;

// ---------- Buscador: apellido, DNI o email ----------
$busqueda = campo($_GET, "q");
$filtroRol = $esAdmin ? (int) campo($_GET, "rol") : ROL_CLIENTE;
$estado = campo($_GET, "estado");   // "" = todos, "activos", "inactivos"

$sql = "SELECT u.*, r.nombre AS nombre_rol,
               (SELECT COUNT(*) FROM cuentas c WHERE c.id_usuario = u.id_usuario AND c.activa = 1) AS cantidad_cuentas
        FROM usuarios u
        JOIN roles r ON r.id_rol = u.id_rol
        WHERE 1 = 1";
$tipos = "";
$parametros = [];

if ($busqueda !== "") {
    $sql .= " AND (u.apellido LIKE ? OR u.nombre LIKE ? OR u.dni LIKE ? OR u.email LIKE ?)";
    $tipos .= "ssss";
    $like = "%" . $busqueda . "%";
    array_push($parametros, $like, $like, $like, $like);
}
if ($filtroRol > 0) {
    $sql .= " AND u.id_rol = ?";
    $tipos .= "i";
    $parametros[] = $filtroRol;
}
if ($estado === "activos") {
    $sql .= " AND u.activo = 1";
} elseif ($estado === "inactivos") {
    $sql .= " AND u.activo = 0";
}

$sql .= " ORDER BY u.id_rol, u.apellido, u.nombre";

$usuarios = consultar($conexion, $sql, $tipos, $parametros);

$titulo = $esAdmin ? "Usuarios" : "Buscar clientes";
require_once(__DIR__ . "/../includes/header.php");

?>

<div class="encabezado-seccion">
    <h1><?php echo e($titulo); ?></h1>
    <div class="acciones">
        <?php if ($esAdmin): ?>
            <a href="usuario_form.php" class="boton">+ Nuevo usuario</a>
        <?php else: ?>
            <a href="../operador/alta_cliente.php" class="boton">+ Nuevo cliente</a>
        <?php endif; ?>
        <a href="<?php echo RAIZ . panelSegunRol(rolActual()); ?>" class="boton boton-secundario">← Panel</a>
    </div>
</div>

<section class="tarjeta">
    <form method="GET" class="filtros">
        <div class="campo">
            <label for="q">Buscar</label>
            <input type="search" id="q" name="q" placeholder="Apellido, nombre, DNI o email…" value="<?php echo e($busqueda); ?>">
        </div>
        <?php if ($esAdmin): ?>
            <div class="campo">
                <label for="rol">Rol</label>
                <select id="rol" name="rol">
                    <option value="0">Todos</option>
                    <?php foreach ([ROL_ADMIN, ROL_OPERADOR, ROL_CLIENTE] as $rol): ?>
                        <option value="<?php echo $rol; ?>" <?php echo $filtroRol === $rol ? "selected" : ""; ?>><?php echo e(nombreRol($rol)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="campo">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                <option value="activos" <?php echo $estado === "activos" ? "selected" : ""; ?>>Activos</option>
                <option value="inactivos" <?php echo $estado === "inactivos" ? "selected" : ""; ?>>Dados de baja</option>
            </select>
        </div>
        <div class="acciones">
            <button type="submit" class="boton">Buscar</button>
            <a href="usuarios.php" class="boton boton-secundario">Limpiar</a>
        </div>
    </form>

    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Apellido y nombre</th>
                    <th>DNI</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Cuentas</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($usuarios) === 0): ?>
                    <tr><td colspan="7" class="vacio">No se encontraron usuarios.</td></tr>
                <?php endif; ?>
                <?php foreach ($usuarios as $u): ?>
                    <?php $esYo = (int) $u["id_usuario"] === (int) $_SESSION["id_usuario"]; ?>
                    <tr class="<?php echo $u["activo"] ? "" : "inactivo"; ?>">
                        <td><?php echo e($u["apellido"] . ", " . $u["nombre"]); ?><?php echo $esYo ? " <span class=\"texto-suave\">(vos)</span>" : ""; ?></td>
                        <td><?php echo e($u["dni"]); ?></td>
                        <td><?php echo e($u["email"]); ?></td>
                        <td><?php echo e($u["nombre_rol"]); ?></td>
                        <td><?php echo (int) $u["id_rol"] === ROL_CLIENTE ? (int) $u["cantidad_cuentas"] : "—"; ?></td>
                        <td>
                            <?php if (!$u["activo"]): ?>
                                <span class="etiqueta-estado apagado">Baja</span>
                            <?php elseif ($u["debe_cambiar_password"]): ?>
                                <span class="etiqueta-estado alerta">Clave provisoria</span>
                            <?php else: ?>
                                <span class="etiqueta-estado">Activo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="acciones">
                                <?php if ((int) $u["id_rol"] === ROL_CLIENTE && $u["activo"]): ?>
                                    <a href="movimientos.php?q=<?php echo e(urlencode($u["dni"])); ?>" class="boton boton-chico boton-secundario">Cuentas</a>
                                <?php endif; ?>

                                <?php if ($esAdmin): ?>
                                    <a href="usuario_form.php?id=<?php echo (int) $u["id_usuario"]; ?>" class="boton boton-chico boton-secundario">Editar</a>

                                    <?php if ($u["activo"]): ?>
                                        <form action="../procesos/procesar_usuario.php" method="POST"
                                              data-confirmar="¿Generar una nueva contraseña provisoria para <?php echo e($u["email"]); ?>?">
                                            <input type="hidden" name="accion" value="reset">
                                            <input type="hidden" name="id_usuario" value="<?php echo (int) $u["id_usuario"]; ?>">
                                            <button type="submit" class="boton boton-chico boton-secundario">Resetear clave</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($u["activo"] && !$esYo): ?>
                                        <form action="../procesos/procesar_usuario.php" method="POST"
                                              data-confirmar="¿Dar de baja a <?php echo e($u["nombre"] . " " . $u["apellido"]); ?>? No podrá volver a ingresar.">
                                            <input type="hidden" name="accion" value="baja">
                                            <input type="hidden" name="id_usuario" value="<?php echo (int) $u["id_usuario"]; ?>">
                                            <button type="submit" class="boton boton-chico boton-peligro">Baja</button>
                                        </form>
                                    <?php elseif (!$u["activo"]): ?>
                                        <form action="../procesos/procesar_usuario.php" method="POST">
                                            <input type="hidden" name="accion" value="reactivar">
                                            <input type="hidden" name="id_usuario" value="<?php echo (int) $u["id_usuario"]; ?>">
                                            <button type="submit" class="boton boton-chico">Reactivar</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="texto-suave"><?php echo count($usuarios); ?> usuario(s).</p>
</section>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
