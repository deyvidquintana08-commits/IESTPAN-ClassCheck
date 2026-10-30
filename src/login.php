<?php
require_once __DIR__ . '/config/bootstrap.php';
if (is_logged_in()) redirect(role_home(current_user()['rol']));
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['correo'] ?? ''));
    $password = $_POST['contrasena'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Ingresa un correo válido y tu contraseña.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nombres, apellidos, correo, password_hash, rol, estado FROM usuarios WHERE correo = ? LIMIT 1');
        $stmt->execute([$email]); $user = $stmt->fetch();
        if ($user && $user['estado'] === 'activo' && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id'=>(int)$user['id'], 'nombre'=>$user['nombres'].' '.$user['apellidos'], 'correo'=>$user['correo'], 'rol'=>$user['rol']];
            audit($pdo, (int)$user['id'], 'inicio_sesion', 'Inicio de sesión correcto');
            redirect(role_home($user['rol']));
        }
        if ($user && $user['estado'] !== 'activo') $error = 'La cuenta está pendiente de aprobación o desactivada.';
        else $error = 'Correo o contraseña incorrectos.';
        audit($pdo, $user ? (int)$user['id'] : null, 'inicio_sesion_fallido', 'Intento de acceso');
    }
}
$pageTitle='Iniciar sesión'; $assetPrefix='';
require __DIR__.'/includes/header.php';
?>
<section class="auth-layout">
<div class="hero-panel"><div class="eyebrow">PLATAFORMA ACADÉMICA</div><h1>Tu vida académica,<br><span>en un solo lugar.</span></h1><p>Consulta horarios, asistencias y rendimiento académico desde ClassCheck.</p><div class="role-cards"><div><b>01</b><strong>Administrador</strong><small>Gestión integral del sistema</small></div><div><b>02</b><strong>Docente</strong><small>Asistencias, notas y horarios</small></div><div><b>03</b><strong>Alumno</strong><small>Tu información académica</small></div></div></div>
<div class="auth-card"><div class="eyebrow">BIENVENIDO/A</div><h2>Iniciar sesión</h2><p class="muted">Selecciona tu tipo de acceso e ingresa tus credenciales.</p>
<?php if ($error): ?><div class="alert error" role="alert"><?= h($error) ?></div><?php endif; ?>
<form method="post" action="login.php">
<input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
<label for="rol">Tipo de acceso</label><select id="rol" name="rol_visual" required><option value="administrador">Administrador</option><option value="docente">Docente</option><option value="alumno" selected>Alumno</option></select>
<label for="correo">Correo electrónico</label><input id="correo" name="correo" type="email" autocomplete="username" required maxlength="190" value="<?= h($_POST['correo'] ?? '') ?>">
<label for="contrasena">Contraseña</label><div class="password-wrap"><input id="contrasena" name="contrasena" type="password" autocomplete="current-password" required><button type="button" class="show-password" data-toggle-password="contrasena">Mostrar</button></div>
<p class="hint">El sistema determina los permisos usando el rol guardado en la base de datos; elegir una opción no cambia tu rol.</p>
<button class="btn primary full" type="submit">Ingresar de forma segura</button></form>
<div class="auth-bottom">¿Todavía no tienes cuenta? <a href="registro.php">Crear cuenta</a></div>
<p class="hint">¿Olvidaste tu contraseña? Solicita al administrador que la restablezca. La recuperación por correo no está habilitada en esta versión local.</p>
</div></section>
<?php require __DIR__.'/includes/footer.php'; ?>
