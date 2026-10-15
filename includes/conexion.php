<?php

// Único punto de conexión a la base de datos.
// Se incluye siempre con require_once(__DIR__ . "/../includes/conexion.php")
// (o la ruta que corresponda) para no abrir dos conexiones.

// En Railway los datos llegan por variables de entorno (las define el
// servicio MySQL). En local (XAMPP) se usan los valores por defecto.
$servidor = (getenv("MYSQLHOST") ?: "127.0.0.1") . ":" . (getenv("MYSQLPORT") ?: "3306");
$usuario = getenv("MYSQLUSER") ?: "root";
$password = getenv("MYSQLPASSWORD") !== false ? getenv("MYSQLPASSWORD") : "root";   // En XAMPP por defecto es "" (vacío)
$baseDatos = getenv("DB_NAME") ?: "banco";

// Que los errores de MySQL se lancen como excepciones: así podemos
// hacer rollback de las transacciones con try/catch.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = mysqli_connect($servidor, $usuario, $password, $baseDatos);
} catch (mysqli_sql_exception $ex) {
    die("ERROR DE CONEXIÓN: " . $ex->getMessage());
}

mysqli_set_charset($conexion, "utf8mb4");

// Hora argentina para NOW() / CURRENT_TIMESTAMP (en un servidor en la nube
// MySQL suele estar en UTC).
mysqli_query($conexion, "SET time_zone = '-03:00'");
