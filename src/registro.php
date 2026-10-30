<?php
require_once __DIR__ . '/config/bootstrap.php';
if (is_logged_in()) redirect(role_home(current_user()['rol']));
$error=''; $success=false;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $rol = $_POST['rol'] ?? 'alumno';
    $nombres=trim($_POST['nombres']??''); $apellidos=trim($_POST['apellidos']??'');
    $dni=trim($_POST['dni']??''); $correo=strtolower(trim($_POST['correo']??''));
    $pass=$_POST['contrasena']??''; $confirm=$_POST['confirmar']??'';
    $carrera=(int)($_POST['carrera_id']??0); $modulo=(int)($_POST['modulo_num']??0); $ciclo=(int)($_POST['ciclo_num']??0);
    if (!in_array($rol,['alumno','docente'],true)) $error='El registro público solo permite crear cuentas de alumno o docente.';
    elseif ($nombres===''||$apellidos===''||!filter_var($correo,FILTER_VALIDATE_EMAIL)||strlen($pass)<10||$pass!==$confirm) $error='Completa los campos correctamente. La contraseña debe tener al menos 10 caracteres y coincidir.';
    elseif ($dni!=='' && !preg_match('/^[0-9]{8,12}$/',$dni)) $error='El DNI debe contener entre 8 y 12 dígitos.';
    elseif ($rol==='alumno' && ($carrera<1||$carrera>3||$modulo<1||$modulo>3||$ciclo<1||$ciclo>2)) $error='Selecciona carrera, módulo y ciclo válidos.';
    else {
        // Evitar duplicados por correo o DNI mediante consultas separadas para compatibilidad SQL.
        $q=$pdo->prepare('SELECT id FROM usuarios WHERE correo=?'); $q->execute([$correo]);
        $dup=$q->fetch();
        if (!$dup && $dni!=='') { $q=$pdo->prepare('SELECT id FROM usuarios WHERE dni=?'); $q->execute([$dni]); $dup=$q->fetch(); }
        if ($dup) $error='Ya existe una cuenta con ese correo o DNI.';
        else {
            $estado = $rol==='docente' ? 'pendiente' : 'activo';
            $pdo->beginTransaction();
            try {
                $q=$pdo->prepare('INSERT INTO usuarios(nombres,apellidos,dni,correo,password_hash,rol,estado) VALUES(?,?,?,?,?,?,?)');
                $q->execute([$nombres,$apellidos,$dni?:null,$correo,password_hash($pass,PASSWORD_DEFAULT),$rol,$estado]);
                $uid=(int)$pdo->lastInsertId();
                if ($rol==='alumno') {
                    $q=$pdo->prepare('INSERT INTO matriculas(alumno_id,carrera_id,modulo_num,ciclo_num,estado) VALUES(?,?,?,?,?)');
                    $q->execute([$uid,$carrera,$modulo,$ciclo,'activa']);
                }
                audit($pdo,$uid,'registro_publico','Registro de cuenta: '.$rol);
                $pdo->commit(); $success=true;
            } catch (Throwable $e) { $pdo->rollBack(); $error='No se pudo completar el registro. Verifica los datos e inténtalo nuevamente.'; }
        }
    }
}
$careers=$pdo->query('SELECT id,nombre FROM carreras ORDER BY id')->fetchAll();
$pageTitle='Crear cuenta'; $assetPrefix='';
require __DIR__.'/includes/header.php';
?>
<section class="form-shell"><div class="section-heading"><div class="eyebrow">NUEVA CUENTA</div><h1>Crear cuenta ClassCheck</h1><p class="muted">Por seguridad, las cuentas administrativas solo las crea un administrador autorizado.</p></div>
<?php if ($success): ?><div class="alert success"><?= $rol==='docente'?'Tu solicitud de cuenta docente fue registrada y requiere aprobación del administrador.':'Tu cuenta de alumno fue creada. Ya puedes iniciar sesión.' ?></div><a class="btn primary" href="login.php">Ir al inicio de sesión</a>
<?php else: ?>
<?php if ($error): ?><div class="alert error" role="alert"><?= h($error) ?></div><?php endif; ?>
<form method="post" id="registerForm" class="form-grid">
<input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
<div class="field full"><label for="rol">Tipo de cuenta</label><select name="rol" id="rol" required><option value="alumno" <?= (($_POST['rol']??'alumno')==='alumno')?'selected':'' ?>>Alumno</option><option value="docente" <?= (($_POST['rol']??'')==='docente')?'selected':'' ?>>Docente (requiere aprobación)</option></select></div>
<div class="field"><label for="nombres">Nombres</label><input id="nombres" name="nombres" required maxlength="100" value="<?= h($_POST['nombres']??'') ?>"></div>
<div class="field"><label for="apellidos">Apellidos</label><input id="apellidos" name="apellidos" required maxlength="100" value="<?= h($_POST['apellidos']??'') ?>"></div>
<div class="field"><label for="dni">DNI</label><input id="dni" name="dni" inputmode="numeric" pattern="[0-9]{8,12}" maxlength="12" value="<?= h($_POST['dni']??'') ?>"></div>
<div class="field"><label for="correo">Correo electrónico</label><input id="correo" name="correo" type="email" required maxlength="190" value="<?= h($_POST['correo']??'') ?>"></div>
<div class="field"><label for="contrasena">Contraseña (mínimo 10 caracteres)</label><input id="contrasena" name="contrasena" type="password" minlength="10" required></div>
<div class="field"><label for="confirmar">Confirmar contraseña</label><input id="confirmar" name="confirmar" type="password" minlength="10" required></div>
<div id="academicFields" class="field full"><h3>Datos académicos del alumno</h3><div class="form-grid nested">
<div class="field"><label for="carrera_id">Carrera técnica</label><select name="carrera_id" id="carrera_id"><option value="">Seleccionar carrera</option><?php foreach($careers as $c): ?><option value="<?= (int)$c['id'] ?>" <?= ((int)($_POST['carrera_id']??0)===(int)$c['id'])?'selected':'' ?>><?= h($c['nombre']) ?></option><?php endforeach; ?></select></div>
<div class="field"><label for="modulo_num">Módulo</label><select name="modulo_num" id="modulo_num"><option value="">Seleccionar módulo</option><?php for($i=1;$i<=3;$i++): ?><option value="<?= $i ?>" <?= ((int)($_POST['modulo_num']??0)===$i)?'selected':'' ?>>Módulo <?= $i ?></option><?php endfor; ?></select></div>
<div class="field"><label for="ciclo_num">Ciclo</label><select name="ciclo_num" id="ciclo_num"><option value="">Seleccionar ciclo</option><?php for($i=1;$i<=2;$i++): ?><option value="<?= $i ?>" <?= ((int)($_POST['ciclo_num']??0)===$i)?'selected':'' ?>>Ciclo <?= $i ?></option><?php endfor; ?></select></div>
</div></div>
<div class="field full"><button class="btn primary" type="submit">Crear cuenta</button> <a class="btn secondary" href="login.php">Cancelar</a></div>
</form><?php endif; ?></section>
<?php require __DIR__.'/includes/footer.php'; ?>
