<?php
// Reemplazo mínimo de sendmail para Railway: PHP le pasa el mail completo por
// stdin (cuando se llama a mail()) y este script lo entrega por SMTP a Mailpit.
// Se activa con php/mail.ini (ver PHP_INI_SCAN_DIR en el README).

// Solo desde la línea de comandos (no tiene que poder abrirse desde el navegador).
if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

$host = getenv("SMTP_HOST") ?: "127.0.0.1";
$port = (int) (getenv("SMTP_PORT") ?: 1025);

$mensaje = stream_get_contents(STDIN);
$mensaje = str_replace(["\r\n", "\r"], "\n", $mensaje);

// Separar encabezados del cuerpo para sacar remitente y destinatarios.
[$cabecera] = explode("\n\n", $mensaje, 2);
$cabecera = preg_replace("/\n[ \t]+/", " ", $cabecera);

$remitente = "no-responder@banco.local";
$destinatarios = [];
foreach (explode("\n", $cabecera) as $linea) {
    if (!preg_match('/^(From|To|Cc|Bcc):\s*(.*)$/i', $linea, $m)) {
        continue;
    }
    preg_match_all('/[^\s<>,"]+@[^\s<>,"]+/', $m[2], $direcciones);
    if (strcasecmp($m[1], "From") === 0) {
        $remitente = $direcciones[0][0] ?? $remitente;
    } else {
        $destinatarios = array_merge($destinatarios, $direcciones[0]);
    }
}

// El Bcc no tiene que quedar visible en el mail entregado.
$mensaje = preg_replace('/^Bcc:.*\n(?:[ \t].*\n)*/mi', "", $mensaje);

if (!$destinatarios) {
    fwrite(STDERR, "sendmail.php: el mail no tiene destinatarios\n");
    exit(1);
}

$smtp = @fsockopen($host, $port, $errno, $errstr, 10);
if (!$smtp) {
    fwrite(STDERR, "sendmail.php: no se pudo conectar a $host:$port ($errstr)\n");
    exit(1);
}

function comando($smtp, ?string $linea, string $codigoEsperado): void
{
    if ($linea !== null) {
        fwrite($smtp, $linea . "\r\n");
    }
    do {
        $respuesta = fgets($smtp, 512);
    } while ($respuesta !== false && ($respuesta[3] ?? " ") === "-");
    if ($respuesta === false || strncmp($respuesta, $codigoEsperado, 3) !== 0) {
        fwrite(STDERR, "sendmail.php: respuesta inesperada: " . trim((string) $respuesta) . "\n");
        exit(1);
    }
}

comando($smtp, null, "220");
comando($smtp, "HELO banco.local", "250");
comando($smtp, "MAIL FROM:<$remitente>", "250");
foreach (array_unique($destinatarios) as $destinatario) {
    comando($smtp, "RCPT TO:<$destinatario>", "250");
}
comando($smtp, "DATA", "354");

// Normalizar a CRLF y duplicar los puntos al inicio de línea (regla de SMTP).
$datos = preg_replace('/^\./m', "..", rtrim($mensaje, "\n"));
fwrite($smtp, str_replace("\n", "\r\n", $datos) . "\r\n.\r\n");
comando($smtp, null, "250");
comando($smtp, "QUIT", "221");
fclose($smtp);
