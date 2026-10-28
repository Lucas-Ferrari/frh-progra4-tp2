<?php

// Funciones y constantes que usa todo el sitio.
// Se incluye (directa o indirectamente) en todas las páginas.

date_default_timezone_set("America/Argentina/Buenos_Aires");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------------------------------------------------
// Roles (coinciden con los id_rol de la tabla roles)
// ---------------------------------------------------------------------
const ROL_ADMIN = 1;
const ROL_OPERADOR = 2;
const ROL_CLIENTE = 3;

// Tiempo máximo de inactividad antes de cerrar la sesión (en segundos).
const SESION_MAX_INACTIVIDAD = 30 * 60;

// ---------------------------------------------------------------------
// RAIZ: prefijo relativo para llegar a la raíz del proyecto desde la
// página que se está ejecutando ("" desde index.php, "../" desde
// admin/panel.php). Así los links y redirecciones funcionan sin importar
// en qué carpeta esté instalado el proyecto.
// ---------------------------------------------------------------------
$carpetaProyecto = str_replace("\\", "/", realpath(__DIR__ . "/.."));
$carpetaScript = str_replace("\\", "/", realpath(dirname($_SERVER["SCRIPT_FILENAME"])));
$subcarpeta = trim(substr($carpetaScript, strlen($carpetaProyecto)), "/");
define("RAIZ", $subcarpeta === "" ? "" : str_repeat("../", substr_count($subcarpeta, "/") + 1));

// Escapa un texto antes de imprimirlo en el HTML (evita XSS).
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
}

// Redirige a una ruta relativa a la raíz del proyecto y corta la ejecución.
function redirigir($ruta)
{
    header("Location: " . RAIZ . $ruta);
    exit;
}

function estaLogueado()
{
    return isset($_SESSION["id_usuario"]);
}

function rolActual()
{
    return isset($_SESSION["id_rol"]) ? (int) $_SESSION["id_rol"] : 0;
}

// Panel al que va cada rol después de loguearse.
function panelSegunRol($idRol)
{
    switch ((int) $idRol) {
        case ROL_ADMIN:
            return "admin/panel.php";
        case ROL_OPERADOR:
            return "operador/panel.php";
        default:
            return "cliente/panel.php";
    }
}

function nombreRol($idRol)
{
    $nombres = [ROL_ADMIN => "Administrador", ROL_OPERADOR => "Operador", ROL_CLIENTE => "Cliente"];
    return isset($nombres[$idRol]) ? $nombres[$idRol] : "Desconocido";
}

// Contraseña provisoria aleatoria. random_int() es criptográficamente
// seguro (rand() no lo es).
function generarPasswordProvisoria($longitud = 8)
{
    $pool = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
    $password = "";

    for ($i = 0; $i < $longitud; $i++) {
        $password .= $pool[random_int(0, strlen($pool) - 1)];
    }

    return $password;
}

// Formatea un importe: "$ 1.234,50" o "US$ 1.234,50".
function formatoMonto($monto, $moneda = "ARS")
{
    $simbolo = $moneda === "USD" ? "US$" : "$";
    return $simbolo . " " . number_format((float) $monto, 2, ",", ".");
}

function formatoFecha($fecha, $conHora = true)
{
    return date($conHora ? "d/m/Y H:i" : "d/m/Y", strtotime($fecha));
}

// Lee un campo de $_POST/$_GET ya recortado (string vacío si no vino).
function campo($origen, $nombre)
{
    return isset($origen[$nombre]) ? trim((string) $origen[$nombre]) : "";
}

// ---------------------------------------------------------------------
// Recordar lo que el usuario escribió cuando un formulario vuelve con
// error, para que no tenga que cargar todo de nuevo. Dura un solo pedido.
// ---------------------------------------------------------------------
$FORM_ANTERIOR = isset($_SESSION["form_anterior"]) ? $_SESSION["form_anterior"] : [];
unset($_SESSION["form_anterior"]);

function recordarFormulario($datos)
{
    // Las contraseñas nunca se guardan.
    foreach ($datos as $clave => $valor) {
        if (stripos($clave, "password") !== false) {
            unset($datos[$clave]);
        }
    }
    $_SESSION["form_anterior"] = $datos;
}

function valorAnterior($nombre, $porDefecto = "")
{
    global $FORM_ANTERIOR;
    return isset($FORM_ANTERIOR[$nombre]) ? $FORM_ANTERIOR[$nombre] : $porDefecto;
}

// Vuelve al formulario con un error, recordando lo que se había cargado.
function volverConError($ruta, $codigoError)
{
    recordarFormulario($_POST);
    $separador = strpos($ruta, "?") === false ? "?" : "&";
    redirigir($ruta . $separador . "error=" . $codigoError);
}

// ---------------------------------------------------------------------
// Validaciones del lado del servidor
// ---------------------------------------------------------------------
function dniValido($dni)
{
    return preg_match('/^[0-9]{7,8}$/', $dni) === 1;
}

function emailValido($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false && strlen($email) <= 100;
}

function fechaValida($fecha)
{
    $d = DateTime::createFromFormat("Y-m-d", $fecha);
    return $d !== false && $d->format("Y-m-d") === $fecha && $fecha <= date("Y-m-d");
}

const PASSWORD_LONGITUD_MINIMA = 6;

// ---------------------------------------------------------------------
// Mensajes de éxito/error que llegan por la URL (?ok=... / ?error=...)
// ---------------------------------------------------------------------
function mostrarMensajes()
{
    $errores = [
        "sesion" => "Tu sesión expiró o no iniciaste sesión. Ingresá nuevamente.",
        "credenciales" => "Email o contraseña incorrectos.",
        "permisos" => "No tenés permisos para acceder a esa sección.",
        "inactivo" => "Tu usuario está dado de baja. Comunicate con el banco.",
        "tipo" => "Elegí un tipo de acceso válido.",
        "campos" => "Completá todos los campos obligatorios.",
        "email" => "El email ingresado no es válido.",
        "dni" => "El DNI debe tener 7 u 8 dígitos numéricos.",
        "fecha" => "La fecha ingresada no es válida.",
        "password_corta" => "La contraseña debe tener al menos " . PASSWORD_LONGITUD_MINIMA . " caracteres.",
        "password_distinta" => "Las contraseñas no coinciden.",
        "password_actual" => "La contraseña actual no es correcta.",
        "password_igual" => "La contraseña nueva tiene que ser distinta de la actual.",
        "duplicado" => "Ya existe un usuario con ese email o DNI.",
        "email_duplicado" => "Ese email ya está en uso por otro usuario.",
        "monto" => "El monto debe ser un número mayor a 0.",
        "saldo" => "Saldo insuficiente: la operación dejaría la cuenta en negativo.",
        "cuenta" => "La cuenta no existe o no está disponible.",
        "cuenta_destino" => "La cuenta destino no existe, está inactiva o es la misma cuenta de origen.",
        "moneda" => "Las cuentas de origen y destino deben tener la misma moneda.",
        "cuenta_saldo" => "No se puede dar de baja una cuenta con saldo distinto de 0.",
        "no_editable" => "Las transferencias no se pueden modificar ni eliminar (afectan a dos cuentas).",
        "auto_baja" => "No podés darte de baja a vos mismo.",
        "auto_rol" => "No podés quitarte el rol de administrador a vos mismo.",
        "rol" => "Rol inválido.",
        "no_encontrado" => "El registro solicitado no existe.",
        "codigo" => "El código ingresado no es correcto.",
        "codigo_vencido" => "El código venció o no hay un pedido en curso. Pedí uno nuevo.",
        "codigo_intentos" => "Demasiados intentos fallidos. Pedí un código nuevo.",
        "db" => "Ocurrió un error al guardar los datos. Intentá nuevamente.",
    ];

    $exitos = [
        "registro" => "¡Registro exitoso! Ya podés ingresar con tu email y contraseña.",
        "logout" => "Cerraste sesión correctamente.",
        "password" => "Contraseña actualizada correctamente.",
        "password_recuperada" => "Listo, ya podés ingresar con tu contraseña nueva.",
        "contacto" => "¡Gracias! Recibimos tu consulta y te responderemos a la brevedad.",
        "contacto_sin_mail" => "Recibimos tu consulta y quedó registrada. (El aviso por mail no pudo enviarse desde este servidor.)",
        "datos" => "Tus datos se actualizaron correctamente.",
        "transferencia" => "Transferencia realizada correctamente.",
        "alta" => "Alta realizada correctamente.",
        "modificacion" => "Cambios guardados correctamente.",
        "baja" => "Baja realizada correctamente.",
        "baja_logica" => "El registro tenía movimientos asociados, así que se dio de baja lógica (queda inactivo).",
        "reactivado" => "Registro reactivado correctamente.",
        "reset" => "Contraseña reseteada correctamente.",
        "leido" => "Consulta marcada como leída.",
        "movimiento" => "Movimiento registrado correctamente.",
        "cliente" => "Cliente cargado correctamente.",
    ];

    if (isset($_GET["error"]) && isset($errores[$_GET["error"]])) {
        echo '<div class="mensaje mensaje-error" role="alert">' . e($errores[$_GET["error"]]) . '</div>';
    }

    if (isset($_GET["ok"]) && isset($exitos[$_GET["ok"]])) {
        echo '<div class="mensaje mensaje-ok" role="status">' . e($exitos[$_GET["ok"]]) . '</div>';
    }

    // Contraseña provisoria recién generada (alta de usuario / reset):
    // se guarda una sola vez en la sesión y se muestra una sola vez.
    if (isset($_SESSION["flash_password"])) {
        $flash = $_SESSION["flash_password"];
        unset($_SESSION["flash_password"]);
        echo '<div class="mensaje mensaje-info">Contraseña provisoria de <strong>' . e($flash["email"]) .
            '</strong>: <code class="password-provisoria">' . e($flash["password"]) .
            '</code><br>Entregásela al usuario: deberá cambiarla en su primer ingreso.</div>';
    }
}

// ---------------------------------------------------------------------
// Helpers de base de datos (siempre con sentencias preparadas)
// ---------------------------------------------------------------------

// Ejecuta un SELECT preparado y devuelve todas las filas como arrays asociativos.
// $tipos es el string de bind_param ("i", "ss", etc.).
function consultar($conexion, $sql, $tipos = "", $parametros = [])
{
    $stmt = mysqli_prepare($conexion, $sql);
    if ($tipos !== "") {
        mysqli_stmt_bind_param($stmt, $tipos, ...$parametros);
    }
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $filas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $filas;
}

// Igual que consultar() pero devuelve solo la primera fila (o null).
function consultarUno($conexion, $sql, $tipos = "", $parametros = [])
{
    $filas = consultar($conexion, $sql, $tipos, $parametros);
    return count($filas) > 0 ? $filas[0] : null;
}

// Ejecuta un INSERT/UPDATE/DELETE preparado. Devuelve las filas afectadas.
function ejecutar($conexion, $sql, $tipos = "", $parametros = [])
{
    $stmt = mysqli_prepare($conexion, $sql);
    if ($tipos !== "") {
        mysqli_stmt_bind_param($stmt, $tipos, ...$parametros);
    }
    mysqli_stmt_execute($stmt);
    $afectadas = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $afectadas;
}

// ¿Existe otro usuario con ese email o DNI? ($excluirId para modificaciones)
function usuarioDuplicado($conexion, $email, $dni, $excluirId = 0)
{
    $fila = consultarUno(
        $conexion,
        "SELECT id_usuario FROM usuarios WHERE (email = ? OR dni = ?) AND id_usuario <> ?",
        "ssi",
        [$email, $dni, $excluirId]
    );
    return $fila !== null;
}

// Valida los datos personales de un formulario de usuario.
// Devuelve el código de error (para ?error=) o null si está todo bien.
function validarDatosPersonales($nombre, $apellido, $dni, $fechaNacimiento, $email)
{
    if ($nombre === "" || $apellido === "" || $dni === "" || $fechaNacimiento === "" || $email === "") {
        return "campos";
    }
    if (mb_strlen($nombre) > 50 || mb_strlen($apellido) > 50) {
        return "campos";
    }
    if (!dniValido($dni)) {
        return "dni";
    }
    if (!fechaValida($fechaNacimiento)) {
        return "fecha";
    }
    if (!emailValido($email)) {
        return "email";
    }
    return null;
}

// Da de alta un usuario con contraseña provisoria (debe cambiarla en su
// primer ingreso). Si es cliente, le abre una caja de ahorro en pesos.
// Devuelve la contraseña provisoria en claro para mostrársela al empleado.
function crearUsuarioConProvisoria($conexion, $nombre, $apellido, $dni, $fechaNacimiento, $email, $idRol)
{
    $passwordProvisoria = generarPasswordProvisoria();
    $hash = password_hash($passwordProvisoria, PASSWORD_DEFAULT);

    mysqli_begin_transaction($conexion);
    try {
        ejecutar(
            $conexion,
            "INSERT INTO usuarios (nombre, apellido, dni, fecha_nacimiento, email, password, debe_cambiar_password, id_rol)
             VALUES (?, ?, ?, ?, ?, ?, 1, ?)",
            "ssssssi",
            [$nombre, $apellido, $dni, $fechaNacimiento, $email, $hash, $idRol]
        );
        $idUsuario = mysqli_insert_id($conexion);

        if ($idRol === ROL_CLIENTE) {
            crearCuenta($conexion, $idUsuario, "Caja de ahorro", "ARS");
        }

        mysqli_commit($conexion);
    } catch (mysqli_sql_exception $ex) {
        mysqli_rollback($conexion);
        return null;
    }

    // Se guarda en la sesión para mostrarla UNA vez en la página siguiente.
    $_SESSION["flash_password"] = ["email" => $email, "password" => $passwordProvisoria];

    return $passwordProvisoria;
}

// Genera un número de cuenta de 10 dígitos que no exista todavía.
function generarNumeroCuenta($conexion)
{
    do {
        $numero = "1001" . str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);
        $existe = consultarUno($conexion, "SELECT id_cuenta FROM cuentas WHERE numero_cuenta = ?", "s", [$numero]);
    } while ($existe !== null);

    return $numero;
}

// Crea una cuenta y, si hay saldo inicial, registra el depósito
// correspondiente (el saldo siempre es la suma de los movimientos).
// Debe llamarse dentro de una transacción si se usa saldo inicial.
function crearCuenta($conexion, $idUsuario, $tipo, $moneda, $saldoInicial = 0.0)
{
    $numero = generarNumeroCuenta($conexion);

    ejecutar(
        $conexion,
        "INSERT INTO cuentas (numero_cuenta, tipo, moneda, saldo, id_usuario) VALUES (?, ?, ?, ?, ?)",
        "sssdi",
        [$numero, $tipo, $moneda, $saldoInicial, $idUsuario]
    );
    $idCuenta = mysqli_insert_id($conexion);

    if ($saldoInicial > 0) {
        ejecutar(
            $conexion,
            "INSERT INTO movimientos (id_cuenta, tipo, monto, descripcion) VALUES (?, 'Depósito', ?, 'Saldo inicial')",
            "id",
            [$idCuenta, $saldoInicial]
        );
    }

    return $idCuenta;
}

// Movimientos de una cuenta, con filtros opcionales del buscador:
// texto en la descripción y rango de fechas (formato Y-m-d).
function buscarMovimientos($conexion, $idCuenta, $texto = "", $desde = "", $hasta = "")
{
    $sql = "SELECT * FROM movimientos WHERE id_cuenta = ?";
    $tipos = "i";
    $parametros = [$idCuenta];

    if ($texto !== "") {
        $sql .= " AND descripcion LIKE ?";
        $tipos .= "s";
        $parametros[] = "%" . $texto . "%";
    }
    if ($desde !== "" && fechaValida($desde)) {
        $sql .= " AND fecha >= ?";
        $tipos .= "s";
        $parametros[] = $desde . " 00:00:00";
    }
    if ($hasta !== "" && fechaValida($hasta)) {
        $sql .= " AND fecha <= ?";
        $tipos .= "s";
        $parametros[] = $hasta . " 23:59:59";
    }

    $sql .= " ORDER BY fecha DESC, id_movimiento DESC";

    return consultar($conexion, $sql, $tipos, $parametros);
}

// Convierte el texto de un monto ("1500", "1500.50", "1500,50") en número.
// Devuelve null si no es un número válido mayor a 0.
function leerMonto($texto)
{
    $texto = str_replace(",", ".", trim((string) $texto));
    if (!is_numeric($texto)) {
        return null;
    }
    $monto = round((float) $texto, 2);
    return ($monto > 0 && $monto < 10000000000) ? $monto : null;
}

const TIPOS_CUENTA = ["Caja de ahorro", "Cuenta corriente"];
const MONEDAS = ["ARS", "USD"];
