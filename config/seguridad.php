<?php
function h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . $path); exit; }
function is_logged_in(): bool { return isset($_SESSION['user']['id'], $_SESSION['user']['rol']); }
function current_user(): array { return $_SESSION['user'] ?? []; }
function role_home(string $role): string {
    return match ($role) {
        'administrador' => 'administrador/index.php',
        'docente' => 'docente/index.php',
        'alumno' => 'alumno/index.php',
        default => 'login.php',
    };
}
function require_login(): void {
    if (!is_logged_in()) { flash('error', 'Inicia sesión para continuar.'); redirect('../login.php'); }
}
function require_role(string ...$roles): void {
    require_login();
    if (!in_array(current_user()['rol'], $roles, true)) {
        http_response_code(403);
        exit('Acceso denegado: tu cuenta no tiene permisos para esta operación.');
    }
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419); exit('La sesión del formulario venció. Regresa e inténtalo nuevamente.');
    }
}
function flash(string $type, string $message): void { $_SESSION['flash'] = ['type'=>$type, 'message'=>$message]; }
function get_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
function audit(PDO $pdo, ?int $userId, string $action, string $details = ''): void {
    $stmt = $pdo->prepare('INSERT INTO auditoria (usuario_id, accion, detalles, ip, user_agent) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $action, mb_substr($details, 0, 1000), $_SERVER['REMOTE_ADDR'] ?? null, mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250)]);
}
function enforce_post(): void { if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido.'); } }
