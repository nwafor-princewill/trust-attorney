<?php
require_once __DIR__ . '/db.php';

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = db()->prepare('SELECT id, full_name, email, phone, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $cache = $stmt->fetch() ?: null;
    return $cache;
}

function require_login(): array {
    $u = current_user();
    if (!$u) {
        header('Location: login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? 'dashboard.php'));
        exit;
    }
    return $u;
}

function current_admin(): ?array {
    if (empty($_SESSION['admin_id'])) return null;
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = db()->prepare('SELECT id, username, created_at FROM admins WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $cache = $stmt->fetch() ?: null;
    return $cache;
}

function require_admin(): array {
    $a = current_admin();
    if (!$a) {
        header('Location: login.php');
        exit;
    }
    return $a;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

function flash_set(string $msg, string $type = 'success'): void {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function flash_get(): ?array {
    if (empty($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function status_badge(string $status): string {
    $map = [
        'pending'   => ['Pending Review', '#b45309', '#fef3c7'],
        'in_review' => ['In Review', '#1d4ed8', '#dbeafe'],
        'approved'  => ['Approved', '#15803d', '#dcfce7'],
        'rejected'  => ['Rejected', '#b91c1c', '#fee2e2'],
    ];
    [$label, $fg, $bg] = $map[$status] ?? [ucfirst($status), '#334155', '#e2e8f0'];
    return '<span class="badge" style="color:' . $fg . ';background:' . $bg . '">' . $label . '</span>';
}
