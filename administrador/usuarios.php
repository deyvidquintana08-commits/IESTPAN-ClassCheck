<?php
require_once __DIR__.'/../config/bootstrap.php'; require_role('administrador');
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf(); $id=(int)($_POST['id']??0); $action=$_POST['action']??'';
    if ($id===(int)current_user()['id'] && ($action==='disable' || ($_POST['rol']??'')!=='administrador')) { flash('error','No puedes desactivar tu propia cuenta ni quitarte el rol administrativo.'); redirect('usuarios.php'); }
    $s=$pdo->prepare('SELECT id,rol FROM usuarios WHERE id=?'); $s->execute([$id]); $target=$s->fetch();
    if ($target) {
        if ($action==='approve' && $target['rol']==='docente') $pdo->prepare("UPDATE usuarios SET estado='activo' WHERE id=?")->execute([$id]);
        elseif ($action==='disable') $pdo->prepare("UPDATE usuarios SET estado='inactivo' WHERE id=?")->execute([$id]);
        elseif ($action==='enable') $pdo->prepare("UPDATE usuarios SET estado='activo' WHERE id=?")->execute([$id]);
        elseif ($action==='role' && in_array($_POST['rol']??'', ['administrador','docente','alumno'],true)) $pdo->prepare('UPDATE usuarios SET rol=? WHERE id=?')->execute([$_POST['rol'],$id]);
        audit($pdo,(int)current_user()['id'],'gestion_usuario',"Acción {$action}; usuario {$id}");
        flash('success','La operación fue procesada.');
    }
    redirect('usuarios.php');
}
$rows=$pdo->query('SELECT u.id,u.nombres,u.apellidos,u.correo,u.rol,u.estado,u.created_at,m.carrera_id,m.modulo_num,m.ciclo_num,c.nombre AS carrera FROM usuarios u LEFT JOIN matriculas m ON m.alumno_id=u.id AND m.estado="activa" LEFT JOIN carreras c ON c.id=m.carrera_id ORDER BY u.created_at DESC')->fetchAll();
$pageTitle='Gestión de usuarios';$assetPrefix='../';require __DIR__.'/../includes/header.php';
?>
<div class="dashboard-head"><div><div class="eyebrow">ADMINISTRACIÓN</div><h1>Usuarios y roles</h1><p class="muted">Las cuentas administrativas solo pueden ser asignadas desde este panel o mediante el instalador inicial.</p></div><a class="btn secondary" href="index.php">Volver al panel</a></div>
<div class="table-wrap"><table><thead><tr><th>Usuario</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Datos académicos</th><th>Acciones</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?= h($r['nombres'].' '.$r['apellidos']) ?><small class="table-sub">ID <?= (int)$r['id'] ?></small></td><td><?= h($r['correo']) ?></td><td><span class="status"><?= h($r['rol']) ?></span></td><td><?= h($r['estado']) ?></td><td><?= $r['carrera']?h($r['carrera'].' · Módulo '.$r['modulo_num'].' · Ciclo '.$r['ciclo_num']):'—' ?></td><td class="actions">
<form method="post"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<?php if($r['rol']==='docente' && $r['estado']==='pendiente'): ?><button name="action" value="approve" class="btn small primary">Aprobar</button><?php endif; ?>
<?php if($r['estado']==='activo'): ?><button name="action" value="disable" class="btn small danger" data-confirm="¿Desactivar esta cuenta?">Desactivar</button><?php else: ?><button name="action" value="enable" class="btn small secondary">Activar</button><?php endif; ?>
<select name="rol" aria-label="Nuevo rol"><?php foreach(['alumno','docente','administrador'] as $role): ?><option value="<?= $role ?>" <?= $r['rol']===$role?'selected':'' ?>><?= ucfirst($role) ?></option><?php endforeach; ?></select><button name="action" value="role" class="btn small secondary">Cambiar rol</button>
</form></td></tr><?php endforeach; ?></tbody></table></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
