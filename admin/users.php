<?php
/** Управление пользователями – только роль admin. */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_role('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($id === (int) $me['id'] && $action !== 'create') {
        flash('Нельзя менять роль или блокировать собственную учётную запись.', 'error');
    } elseif ($action === 'role' && isset(ROLES[$_POST['role'] ?? ''])) {
        db()->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$_POST['role'], $id]);
        flash('Роль изменена.');
    } elseif ($action === 'block') {
        db()->prepare('UPDATE users SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
        flash('Статус учётной записи изменён.');
    } elseif ($action === 'create') {
        $login = trim((string) $_POST['login']);
        $pass = (string) $_POST['password'];
        $role = $_POST['role'] ?? 'client';
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,40}$/', $login) || strlen($pass) < 8 || !isset(ROLES[$role])
            || !filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            flash('Проверьте данные: логин латиницей, пароль от 8 символов, корректный e-mail.', 'error');
        } else {
            try {
                db()->prepare('INSERT INTO users (login, password_hash, name, email, role) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$login, password_hash($pass, PASSWORD_DEFAULT), trim((string) $_POST['name']) ?: $login, $_POST['email'], $role]);
                flash('Пользователь создан.');
            } catch (PDOException $e) {
                flash('Логин или e-mail уже заняты.', 'error');
            }
        }
    }
    redirect('admin/users.php');
}
$rows = db()->query('SELECT * FROM users ORDER BY id')->fetchAll();
$title = 'Пользователи';
$crumbs = [['Панель управления', url('admin/index.php')], ['Пользователи']];
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_nav.php';
?>
<h1>Пользователи и роли</h1>
<table class="data">
    <thead><tr><th>ID</th><th>Логин</th><th>Имя</th><th>E-mail</th><th>Роль</th><th>Статус</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $u): ?>
        <tr><td><?= (int) $u['id'] ?></td><td><?= e($u['login']) ?></td><td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td>
            <td><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $u['id'] ?>"><input type="hidden" name="action" value="role">
                <select name="role" onchange="this.form.submit()" aria-label="Роль <?= e($u['login']) ?>"><?php foreach (ROLES as $k => $l): ?><option value="<?= $k ?>"<?= $k === $u['role'] ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></form></td>
            <td><?= $u['is_active'] ? 'активна' : '<em>заблокирована</em>' ?></td>
            <td><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                <button class="link" name="action" value="block"><?= $u['is_active'] ? 'Заблокировать' : 'Разблокировать' ?></button></form></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>
<h2>Создать пользователя</h2>
<form method="post" class="form form-wide"><?= csrf_field() ?><input type="hidden" name="action" value="create">
    <label>Логин <input name="login" required></label><label>Имя <input name="name"></label>
    <label>E-mail <input type="email" name="email" required></label><label>Пароль <input type="password" name="password" required minlength="8"></label>
    <label>Роль <select name="role"><?php foreach (ROLES as $k => $l): ?><option value="<?= $k ?>"><?= e($l) ?></option><?php endforeach; ?></select></label>
    <button class="btn" type="submit">Создать</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
