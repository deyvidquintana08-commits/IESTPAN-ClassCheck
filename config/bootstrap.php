<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params(['httponly' => true, 'secure' => $secure, 'samesite' => 'Lax']);
    session_start();
}
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/seguridad.php';
