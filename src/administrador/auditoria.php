<?php
require_once __DIR__.'/../config/bootstrap.php'; require_role('administrador');
$rows=$pdo->query('SELECT a.*,u.correo FROM auditoria a LEFT JOIN usuarios u ON u.id=a.usuario_id ORDER BY a.creado_en DESC LIMIT 500')->fetchAll();
$pageTitle='Auditoría';$assetPrefix='../';require __DIR__.'/../includes/header.php';?>
<div class="dashboard-head"><div><div class="eyebrow">TRAZABILIDAD</div><h1>Auditoría del sistema</h1><p class="muted">Últimos 500 eventos registrados.</p></div><a class="btn secondary" href="index.php">Volver</a></div>
<div class="table-wrap"><table><thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Detalles</th><th>IP</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?= h($r['creado_en'])?></td><td><?= h($r['correo']??'No identificado')?></td><td><?= h($r['accion'])?></td><td><?= h($r['detalles'])?></td><td><?= h($r['ip']??'—')?></td></tr><?php endforeach;?></tbody></table></div>
<?php require __DIR__.'/../includes/footer.php';?>
