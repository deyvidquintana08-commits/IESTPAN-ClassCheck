<?php
require_once __DIR__.'/config/bootstrap.php';
if (is_logged_in()) audit($pdo,(int)current_user()['id'],'cierre_sesion','Cierre de sesión');
$_SESSION=[]; if (ini_get('session.use_cookies')) { $p=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']); }
session_destroy(); header('Location: login.php'); exit;
