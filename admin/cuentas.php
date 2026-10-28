<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN]);

// ---------- Buscador: por número de cuenta, apellido o DNI del titular ----------
$busqueda = campo($_GET, "q");
$estado = campo($_GET, "estado");   // "" = activas, "todas", "inactivas"

$sql = "SELECT c.*, u.nombre, u.apellido, u.dni,
               (SELECT COUNT(*) FROM movimientos m WHERE m.id_cuenta = c.id_cuenta) AS cantidad_movimientos
        FROM cuentas c
        JOIN usuarios u ON u.id_usuario = c.id_usuario
        WHERE 1 = 1";
$tipos = "";
$parametros = [];

if ($busqueda !== "") {
    $sql .= " AND (c.numero_cuenta LIKE ? OR u.apellido LIKE ? OR u.dni LIKE ?)";
    $tipos .= "sss";
    $like = "%" . $busqueda . "%";
    array_push($parametros, $like, $like, $like);
}

if ($estado === "inactivas") {
    $sql .= " AND c.activa = 0";
} elseif ($estado !== "todas") {
    $sql .= " AND c.activa = 1";
}

$sql .= " ORDER BY u.apellido, u.nombre, c.numero_cuenta";

$cuentas = consultar($conexion, $sql, $tipos, $parametros);

$titulo = "Cuentas";
require_once(__DIR__ . "/../includes/header.php");

?>

<div class="encabezado-seccion">
    <h1>Cuentas</h1>
    <div class="acciones">
        <a href="cuenta_form.php" class="boton">+ Nueva cuenta</a>
        <a href="panel.php" class="boton boton-secundario">← Panel</a>
    </div>
</div>

<section class="tarjeta">
    <form method="GET" class="filtros">
        <div class="campo">
            <label for="q">Buscar</label>
            <input type="search" id="q" name="q" placeholder="N.º de cuenta, apellido o DNI del titular…" value="<?php echo e($busqueda); ?>">
        </div>
        <div class="campo">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Activas</option>
                <option value="inactivas" <?php echo $estado === "inactivas" ? "selected" : ""; ?>>Dadas de baja</option>
                <option value="todas" <?php echo $estado === "todas" ? "selected" : ""; ?>>Todas</option>
            </select>
        </div>
        <div class="acciones">
            <button type="submit" class="boton">Buscar</button>
            <a href="cuentas.php" class="boton boton-secundario">Limpiar</a>
        </div>
    </form>

    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Titular</th>
                    <th>DNI</th>
                    <th>Tipo</th>
                    <th>Moneda</th>
                    <th class="texto-derecha">Saldo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($cuentas) === 0): ?>
                    <tr><td colspan="8" class="vacio">No se encontraron cuentas.</td></tr>
                <?php endif; ?>
                <?php foreach ($cuentas as $cuenta): ?>
                    <tr class="<?php echo $cuenta["activa"] ? "" : "inactivo"; ?>">
                        <td><?php echo e($cuenta["numero_cuenta"]); ?></td>
                        <td><?php echo e($cuenta["apellido"] . ", " . $cuenta["nombre"]); ?></td>
                        <td><?php echo e($cuenta["dni"]); ?></td>
                        <td><?php echo e($cuenta["tipo"]); ?></td>
                        <td><?php echo e($cuenta["moneda"]); ?></td>
                        <td class="texto-derecha"><?php echo e(formatoMonto($cuenta["saldo"], $cuenta["moneda"])); ?></td>
                        <td>
                            <?php if ($cuenta["activa"]): ?>
                                <span class="etiqueta-estado">Activa</span>
                            <?php else: ?>
                                <span class="etiqueta-estado apagado">Baja</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="acciones">
                                <a href="movimientos.php?id_cuenta=<?php echo (int) $cuenta["id_cuenta"]; ?>" class="boton boton-chico boton-secundario">Movimientos</a>
                                <?php if ($cuenta["activa"]): ?>
                                    <a href="cuenta_form.php?id=<?php echo (int) $cuenta["id_cuenta"]; ?>" class="boton boton-chico boton-secundario">Editar</a>
                                    <form action="../procesos/procesar_cuenta.php" method="POST"
                                          data-confirmar="¿Dar de baja la cuenta <?php echo e($cuenta["numero_cuenta"]); ?>?<?php echo $cuenta["cantidad_movimientos"] > 0 ? " Tiene movimientos, así que quedará inactiva (baja lógica)." : " No tiene movimientos, así que se eliminará definitivamente."; ?>">
                                        <input type="hidden" name="accion" value="baja">
                                        <input type="hidden" name="id_cuenta" value="<?php echo (int) $cuenta["id_cuenta"]; ?>">
                                        <button type="submit" class="boton boton-chico boton-peligro">Baja</button>
                                    </form>
                                <?php else: ?>
                                    <form action="../procesos/procesar_cuenta.php" method="POST">
                                        <input type="hidden" name="accion" value="reactivar">
                                        <input type="hidden" name="id_cuenta" value="<?php echo (int) $cuenta["id_cuenta"]; ?>">
                                        <button type="submit" class="boton boton-chico">Reactivar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="texto-suave"><?php echo count($cuentas); ?> cuenta(s).</p>
</section>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
