<?php
require_once __DIR__.'/config/bootstrap.php';
// Instalador de un solo uso: elimina este archivo después de crear el primer administrador.
$count=(int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='administrador'")->fetchColumn();
$error=''; $done=false;
if ($count>0) { http_response_code(403); exit('Ya existe una cuenta administradora. Por seguridad, elimina crear_administrador.php del servidor.'); }
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $n=trim($_POST['nombres']??''); $a=trim($_POST['apellidos']??''); $email=strtolower(trim($_POST['correo']??'')); $pass=$_POST['contrasena']??'';
    if ($n===''||$a===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<14) $error='Completa los campos. La contraseña debe tener al menos 14 caracteres.';
    else {
        try {
            $s=$pdo->prepare("INSERT INTO usuarios(nombres,apellidos,correo,password_hash,rol,estado) VALUES(?,?,?,?, 'administrador','activo')");
            $s->execute([$n,$a,$email,password_hash($pass,PASSWORD_DEFAULT)]);
            audit($pdo,(int)$pdo->lastInsertId(),'creacion_primer_administrador','Primer administrador creado mediante instalador');
            $done=true;
        } catch (PDOException $e) { $error='No se pudo crear la cuenta. Verifica que el correo no esté registrado.'; }
    }
}
$pageTitle='Crear primer administrador'; $assetPrefix='';
require __DIR__.'/includes/header.php';
?>
<section class="form-shell narrow"><div class="eyebrow">CONFIGURACIÓN INICIAL</div><h1>Crear primer administrador</h1>
<p class="muted">Esta página solo funciona mientras no exista ningún administrador. Después de crear la cuenta, elimina este archivo del servidor.</p>
<?php if($done): ?><div class="alert success">Administrador creado. Elimina ahora crear_administrador.php y luego inicia sesión.</div><a class="btn primary" href="login.php">Ir al inicio de sesión</a>
<?php else: ?><?php if($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
<label for="nombres">Nombres</label><input id="nombres" name="nombres" required>
<label for="apellidos">Apellidos</label><input id="apellidos" name="apellidos" required>
<label for="correo">Correo</label><input id="correo" name="correo" type="email" required>
<label for="contrasena">Contraseña (mínimo 14 caracteres)</label><input id="contrasena" name="contrasena" type="password" minlength="14" required>
<button class="btn primary full" type="submit">Crear administrador</button></form><?php endif; ?></section>
<?php require __DIR__.'/includes/footer.php'; ?>
