<?php

// Movimientos de una cuenta: listado con buscador + alta de depósitos y
// extracciones. La usan administradores y operadores; modificar y
// eliminar movimientos queda solo para administradores.

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN, ROL_OPERADOR]);

$esAdmin = rolActual() === ROL_ADMIN;
$idCuenta = isset($_GET["id_cuenta"]) ? (int) $_GET["id_cuenta"] : 0;
$cuenta = null;

if ($idCuenta > 0) {
    $cuenta = consultarUno(
        $conexion,
        "SELECT c.*, u.nombre, u.apellido, u.dni
         FROM cuentas c JOIN usuarios u ON u.id_usuario = c.id_usuario
         WHERE c.id_cuenta = ?",
        "i",
        [$idCuenta]
    );

    if ($cuenta === null) {
        redirigir("admin/movimientos.php?error=cuenta");
    }
}

if ($cuenta === null) {
    // ---------- Paso 1: elegir la cuenta ----------
    $busquedaCuenta = campo($_GET, "q");
    $cuentasEncontradas = [];

    if ($busquedaCuenta !== "") {
        $like = "%" . $busquedaCuenta . "%";
        $cuentasEncontradas = consultar(
            $conexion,
            "SELECT c.*, u.nombre, u.apellido, u.dni
             FROM cuentas c JOIN usuarios u ON u.id_usuario = c.id_usuario
             WHERE c.activa = 1 AND (c.numero_cuenta LIKE ? OR u.apellido LIKE ? OR u.dni LIKE ?)
             ORDER BY u.apellido, c.numero_cuenta",
            "sss",
            [$like, $like, $like]
        );
    }
} else {
    // ---------- Paso 2: movimientos de la cuenta (con buscador) ----------
    $texto = campo($_GET, "texto");
    $desde = campo($_GET, "desde");
    $hasta = campo($_GET, "hasta");
    $hayFiltros = $texto !== "" || $desde !== "" || $hasta !== "";
    $movimientos = buscarMovimientos($conexion, $idCuenta, $texto, $desde, $hasta);
}

$titulo = "Movimientos";
require_once(__DIR__ . "/../includes/header.php");

?>

<?php if ($cuenta === null): ?>

    <div class="encabezado-seccion">
        <h1>Movimientos</h1>
        <a href="<?php echo RAIZ . panelSegunRol(rolActual()); ?>" class="boton boton-secundario">← Panel</a>
    </div>

    <section class="tarjeta">
        <h2>Buscar cuenta</h2>
        <form method="GET" class="filtros">
            <div class="campo">
                <label for="q">N.º de cuenta, apellido o DNI del titular</label>
                <input type="search" id="q" name="q" required value="<?php echo e($busquedaCuenta); ?>">
            </div>
            <div class="acciones">
                <button type="submit" class="boton">Buscar</button>
            </div>
        </form>

        <?php if ($busquedaCuenta !== ""): ?>
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr><th>Número</th><th>Titular</th><th>DNI</th><th>Tipo</th><th class="texto-derecha">Saldo</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($cuentasEncontradas) === 0): ?>
                            <tr><td colspan="6" class="vacio">No se encontraron cuentas activas.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($cuentasEncontradas as $c): ?>
                            <tr>
                                <td><?php echo e($c["numero_cuenta"]); ?></td>
                                <td><?php echo e($c["apellido"] . ", " . $c["nombre"]); ?></td>
                                <td><?php echo e($c["dni"]); ?></td>
                                <td><?php echo e($c["tipo"] . " " . $c["moneda"]); ?></td>
                                <td class="texto-derecha"><?php echo e(formatoMonto($c["saldo"], $c["moneda"])); ?></td>
                                <td><a href="movimientos.php?id_cuenta=<?php echo (int) $c["id_cuenta"]; ?>" class="boton boton-chico">Operar</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

<?php else: ?>

    <div class="encabezado-seccion">
        <div>
            <h1>Cuenta <?php echo e($cuenta["numero_cuenta"]); ?></h1>
            <p class="texto-suave">
                <?php echo e($cuenta["apellido"] . ", " . $cuenta["nombre"]); ?> (DNI <?php echo e($cuenta["dni"]); ?>) ·
                <?php echo e($cuenta["tipo"] . " en " . $cuenta["moneda"]); ?> ·
                Saldo: <strong><?php echo e(formatoMonto($cuenta["saldo"], $cuenta["moneda"])); ?></strong>
                <?php if (!$cuenta["activa"]): ?><span class="etiqueta-estado apagado">Dada de baja</span><?php endif; ?>
            </p>
        </div>
        <div class="acciones">
            <a href="movimientos.php" class="boton boton-secundario">Otra cuenta</a>
            <?php if ($esAdmin): ?>
                <a href="cuentas.php" class="boton boton-secundario">← Cuentas</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($cuenta["activa"]): ?>
        <section class="tarjeta">
            <h2>Registrar depósito o extracción</h2>
            <form action="../procesos/procesar_movimiento.php" method="POST" class="filtros" novalidate>
                <input type="hidden" name="accion" value="alta">
                <input type="hidden" name="id_cuenta" value="<?php echo (int) $idCuenta; ?>">
                <div class="campo">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo">
                        <option value="Depósito">Depósito</option>
                        <option value="Extracción" <?php echo valorAnterior("tipo") === "Extracción" ? "selected" : ""; ?>>Extracción</option>
                    </select>
                </div>
                <div class="campo">
                    <label for="monto">Monto (<?php echo e($cuenta["moneda"]); ?>)</label>
                    <input type="number" id="monto" name="monto" min="0.01" step="0.01" required data-monto value="<?php echo e(valorAnterior("monto")); ?>">
                </div>
                <div class="campo">
                    <label for="descripcion">Descripción</label>
                    <input type="text" id="descripcion" name="descripcion" maxlength="150" placeholder="Ej.: Depósito por ventanilla" value="<?php echo e(valorAnterior("descripcion")); ?>">
                </div>
                <div class="acciones">
                    <button type="submit" class="boton">Registrar</button>
                </div>
            </form>
        </section>
    <?php endif; ?>

    <section class="tarjeta">
        <h2>Historial</h2>
        <form method="GET" class="filtros">
            <input type="hidden" name="id_cuenta" value="<?php echo (int) $idCuenta; ?>">
            <div class="campo">
                <label for="texto">Descripción</label>
                <input type="search" id="texto" name="texto" value="<?php echo e($texto); ?>">
            </div>
            <div class="campo">
                <label for="desde">Desde</label>
                <input type="date" id="desde" name="desde" value="<?php echo e($desde); ?>">
            </div>
            <div class="campo">
                <label for="hasta">Hasta</label>
                <input type="date" id="hasta" name="hasta" value="<?php echo e($hasta); ?>">
            </div>
            <div class="acciones">
                <button type="submit" class="boton">Buscar</button>
                <?php if ($hayFiltros): ?>
                    <a href="movimientos.php?id_cuenta=<?php echo (int) $idCuenta; ?>" class="boton boton-secundario">Limpiar</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th class="texto-derecha">Monto</th>
                        <?php if ($esAdmin): ?><th>Acciones</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($movimientos) === 0): ?>
                        <tr><td colspan="5" class="vacio">No hay movimientos<?php echo $hayFiltros ? " que coincidan con la búsqueda" : ""; ?>.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td><?php echo e(formatoFecha($mov["fecha"])); ?></td>
                            <td><?php echo e($mov["tipo"]); ?></td>
                            <td><?php echo e($mov["descripcion"]); ?></td>
                            <td class="texto-derecha <?php echo $mov["monto"] < 0 ? "negativo" : "positivo"; ?>"><?php echo e(formatoMonto($mov["monto"], $cuenta["moneda"])); ?></td>
                            <?php if ($esAdmin): ?>
                                <td>
                                    <?php if ($mov["tipo"] !== "Transferencia" && $cuenta["activa"]): ?>
                                        <div class="acciones">
                                            <a href="movimiento_form.php?id=<?php echo (int) $mov["id_movimiento"]; ?>" class="boton boton-chico boton-secundario">Editar</a>
                                            <form action="../procesos/procesar_movimiento.php" method="POST" data-confirmar="¿Eliminar este movimiento? El saldo de la cuenta se va a recalcular.">
                                                <input type="hidden" name="accion" value="baja">
                                                <input type="hidden" name="id_movimiento" value="<?php echo (int) $mov["id_movimiento"]; ?>">
                                                <button type="submit" class="boton boton-chico boton-peligro">Eliminar</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="texto-suave">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="texto-suave"><?php echo count($movimientos); ?> movimiento(s).</p>
    </section>

<?php endif; ?>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
