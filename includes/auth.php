<?php
/** Учётные записи и роли: admin – администратор сайта, manager – администратор салона,
 *  client – клиент. */

const ROLES = ['admin' => 'Администратор сайта', 'manager' => 'Администратор салона', 'client' => 'Клиент'];

function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['uid'])) {
            $st = db()->prepare('SELECT id, login, name, email, phone, role FROM users WHERE id = ? AND is_active = 1');
            $st->execute([$_SESSION['uid']]);
            $user = $st->fetch() ?: null;
        }
    }
    return $user;
}

function has_role(string ...$roles): bool
{
    $u = current_user();
    return $u !== null && in_array($u['role'], $roles, true);
}

function require_login(): array
{
    $u = current_user();
    if ($u === null) {
        flash('Войдите на сайт, чтобы продолжить.', 'info');
        redirect('login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
    }
    return $u;
}

function require_role(string ...$roles): array
{
    $u = require_login();
    if (!in_array($u['role'], $roles, true)) {
        http_response_code(403);
        $title = 'Доступ запрещён';
        $crumbs = [['Ошибка 403']];
        require __DIR__ . '/header.php';
        echo '<section class="page-404"><h1>Доступ запрещён (403)</h1><p>Вашей роли «' . e(ROLES[$u['role']])
            . '» недостаточно прав для этого раздела.</p><p><a class="btn" href="' . e(url()) . '">На главную</a></p></section>';
        require __DIR__ . '/footer.php';
        exit;
    }
    return $u;
}

function login_user(string $login, string $password): bool
{
    $st = db()->prepare('SELECT id, password_hash FROM users WHERE (login = ? OR email = ?) AND is_active = 1');
    $st->execute([$login, $login]);
    $row = $st->fetch();
    if ($row && password_verify($password, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int) $row['id'];
        return true;
    }
    return false;
}
