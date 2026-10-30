<?php
// Ajusta estas variables a tu instalación local de XAMPP.
$DB_HOST = '127.0.0.1';
$DB_NAME = 'classcheck';
$DB_USER = 'root';
$DB_PASS = ''; // En XAMPP suele estar vacío por defecto; configura una contraseña en producción.
try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('No se pudo conectar con la base de datos. Revisa config/conexion.php e importa database/classcheck.sql.');
}
