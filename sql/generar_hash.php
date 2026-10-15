<?php

// Script auxiliar para generar el hash de las contraseñas de prueba.
// Uso: abrir en el navegador (http://localhost/banco/sql/generar_hash.php)
// o ejecutar "php sql/generar_hash.php" y pegar el resultado en banco.sql.

$passwordEnClaro = "123456";

echo password_hash($passwordEnClaro, PASSWORD_DEFAULT);
