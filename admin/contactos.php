<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN]);

$filtro = campo($_GET, "filtro");   // "" = todas, "sin_leer"
$busqueda = campo($_GET, "q");

$sql = "SELECT ct.*, u.dni FROM contactos ct
        LEFT JOIN usuarios u ON u.id_usuario = ct.id_usuario
        WHERE 1 = 1";
$tipos = "";
$parametros = [];

if ($filtro === "sin_leer") {
    $sql .= " AND ct.leido = 0";
}
if ($busqueda !== "") {
    $sql .= " AND (ct.nombre LIKE ? OR ct.email LIKE ? OR ct.asunto LIKE ? OR ct.mensaje LIKE ?)";
    $tipos .= "ssss";
    $like = "%" . $busqueda . "%";
    array_push($parametros, $like, $like, $like, $like);
}
$sql .= " ORDER BY ct.leido, ct.fecha DESC";

$contactos = consultar($conexion, $sql, $tipos, $parametros);

$titulo = "Consultas";
require_once(__DIR__ . "/../includes/header.php");

?>

<div class="encabezado-seccion">
    <h1>Consultas recibidas</h1>
    <a href="panel.php" class="boton boton-secundario">← Panel</a>
</div>

<section class="tarjeta">
    <form method="GET" class="filtros">
        <div class="campo">
            <label for="q">Buscar</label>
            <input type="search" id="q" name="q" placeholder="Nombre, email, asunto o texto" value="<?php echo e($busqueda); ?>">
        </div>
        <div class="campo">
            <label for="filtro">Mostrar</label>
            <select id="filtro" name="filtro">
                <option value="">Todas</option>
                <option value="sin_leer" <?php echo $filtro === "sin_leer" ? "selected" : ""; ?>>Sin leer</option>
            </select>
        </div>
        <div class="acciones">
            <button type="submit" class="boton">Buscar</button>
            <a href="contactos.php" class="boton boton-secundario">Limpiar</a>
        </div>
    </form>

    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>De</th>
                    <th>Asunto y mensaje</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($contactos) === 0): ?>
                    <tr><td colspan="5" class="vacio">No hay consultas.</td></tr>
                <?php endif; ?>
                <?php foreach ($contactos as $c): ?>
                    <tr class="<?php echo $c["leido"] ? "" : "no-leido"; ?>">
                        <td><?php echo e(formatoFecha($c["fecha"])); ?></td>
                        <td>
                            <?php echo e($c["nombre"]); ?><br>
                            <a href="mailto:<?php echo e($c["email"]); ?>"><?php echo e($c["email"]); ?></a>
                            <?php if ($c["id_usuario"] !== null): ?>
                                <br><span class="texto-suave">Cliente registrado (DNI <?php echo e($c["dni"]); ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo e($c["asunto"]); ?></strong><br>
                            <?php echo nl2br(e($c["mensaje"])); ?>
                        </td>
                        <td>
                            <?php if ($c["leido"]): ?>
                                <span class="etiqueta-estado apagado">Leída</span>
                            <?php else: ?>
                                <span class="etiqueta-estado alerta">Sin leer</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="acciones">
                                <?php if (!$c["leido"]): ?>
                                    <form action="../procesos/procesar_contacto_admin.php" method="POST">
                                        <input type="hidden" name="accion" value="leido">
                                        <input type="hidden" name="id_contacto" value="<?php echo (int) $c["id_contacto"]; ?>">
                                        <button type="submit" class="boton boton-chico boton-secundario">Marcar leída</button>
                                    </form>
                                <?php endif; ?>
                                <form action="../procesos/procesar_contacto_admin.php" method="POST" data-confirmar="¿Eliminar la consulta de <?php echo e($c["nombre"]); ?>?">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id_contacto" value="<?php echo (int) $c["id_contacto"]; ?>">
                                    <button type="submit" class="boton boton-chico boton-peligro">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
