<?php
$pageTitle = $pageTitle ?? 'ClassCheck';
$flashMessage = get_flash();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle) ?> · ClassCheck</title>
<link rel="stylesheet" href="<?= h($assetPrefix ?? '') ?>assets/css/style.css">
<script defer src="<?= h($assetPrefix ?? '') ?>assets/js/app.js"></script>
</head>
<body>
<a class="skip-link" href="#contenido">Saltar al contenido</a>
<header class="topbar"><a class="brand" href="<?= h($assetPrefix ?? '') ?>index.php"><span class="brand-mark">CC</span><span>ClassCheck<small>IESTP Alfred Nobel</small></span></a>
<nav aria-label="Navegación principal">
<?php if (is_logged_in()): ?><span class="user-pill"><?= h(current_user()['nombre']) ?> · <?= h(ucfirst(current_user()['rol'])) ?></span><a href="<?= h($assetPrefix ?? '') ?>logout.php">Cerrar sesión</a><?php else: ?><a href="<?= h($assetPrefix ?? '') ?>login.php">Iniciar sesión</a><a href="<?= h($assetPrefix ?? '') ?>registro.php">Crear cuenta</a><?php endif; ?>
</nav></header>
<main id="contenido" class="container">
<?php if ($flashMessage): ?><div class="alert <?= h($flashMessage['type']) ?>" role="status"><?= h($flashMessage['message']) ?></div><?php endif; ?>
