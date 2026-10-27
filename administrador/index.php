<?php
require_once __DIR__.'/../config/bootstrap.php'; require_role('administrador');
$pageTitle='Panel del administrador'; $assetPrefix='../';
$stats=[];
foreach (['usuarios'=>'Usuarios','carreras'=>'Carreras','unidades_didacticas'=>'Unidades didácticas','solicitudes_reapertura'=>'Solicitudes de reapertura'] as $table=>$label) {
    try { $stats[$label]=(int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn(); } catch(Throwable $e) { $stats[$label]=0; }
}
$pending=(int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE estado='pendiente'")->fetchColumn();
$requests=(int)$pdo->query("SELECT COUNT(*) FROM solicitudes_reapertura WHERE estado='pendiente'")->fetchColumn();
require __DIR__.'/../includes/header.php';
?>
<div class="dashboard-head"><div><div class="eyebrow">CENTRO DE CONTROL</div><h1>Panel de administración</h1><p class="muted">Gestiona usuarios y consulta el estado general de ClassCheck.</p></div><span class="status success">Administrador autorizado</span></div>
<div class="stats-grid"><?php foreach($stats as $label=>$value): ?><article class="stat-card"><span><?= h($label) ?></span><strong><?= $value ?></strong></article><?php endforeach; ?></div>
<div class="content-grid"><section class="panel"><h2>Gestión del sistema</h2><div class="action-list">
<a href="usuarios.php"><span>👥</span><div><b>Usuarios y roles</b><small>Consultar cuentas, aprobar docentes, activar/desactivar y cambiar roles.</small></div><strong>→</strong></a>
<a href="academico.php"><span>🎓</span><div><b>Carreras y unidades didácticas</b><small>Consultar carreras, módulos y unidades.</small></div><strong>→</strong></a>
<a href="horarios.php"><span>🗓️</span><div><b>Horarios</b><small>Crear, revisar y eliminar horarios.</small></div><strong>→</strong></a>
<a href="plazos.php"><span>⏱️</span><div><b>Plazos de registro</b><small>Configurar aperturas de 24 horas para asistencia y notas.</small></div><strong>→</strong></a>
<a href="solicitudes.php"><span>📨</span><div><b>Solicitudes de reapertura</b><small>Hay <?= $requests ?> solicitudes pendientes.</small></div><strong>→</strong></a>
<a href="auditoria.php"><span>🧾</span><div><b>Auditoría</b><small>Consultar acciones relevantes registradas.</small></div><strong>→</strong></a>
</div></section><aside class="panel"><h2>Atención requerida</h2><div class="notice"><b><?= $pending ?></b><span>cuentas pendientes de aprobación</span></div><div class="notice"><b><?= $requests ?></b><span>solicitudes de reapertura pendientes</span></div><p class="hint">Antes de usar datos reales, revisa la configuración y cambia las credenciales predeterminadas de MySQL.</p></aside></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
