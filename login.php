<?php
require __DIR__ . '/includes/bootstrap.php';
$next = (string) ($_GET['next'] ?? $_POST['next'] ?? '');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $attempts = $_SESSION['login_attempts'] ?? 0;
    if ($attempts >= 5 && time() - ($_SESSION['login_last'] ?? 0) < 300) {
        $error = 'Слишком много попыток входа. Повторите через 5 минут.';
    } elseif (login_user(trim((string) $_POST['login']), (string) $_POST['password'])) {
        unset($_SESSION['login_attempts']);
        $u = current_user();
        flash('Здравствуйте, ' . $u['name'] . '!');
        if ($next !== '' && str_starts_with($next, '/') && !str_starts_with($next, '//')) {
            header('Location: ' . $next);
            exit;
        }
        redirect($u['role'] === 'client' ? 'cabinet.php' : 'admin/index.php');
    } else {
        $_SESSION['login_attempts'] = $attempts + 1;
        $_SESSION['login_last'] = time();
        $error = 'Неверный логин или пароль.';
    }
}
$title = 'Вход';
$crumbs = [['Вход']];
require __DIR__ . '/includes/header.php';
?>
<h1>Вход в личный кабинет</h1>
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form form-narrow">
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <label>Логин или e-mail <input name="login" required autocomplete="username" value="<?= e($_POST['login'] ?? '') ?>"></label>
    <label>Пароль <input type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn" type="submit">Войти</button>
    <p>Нет учётной записи? <a href="<?= e(url('register.php')) ?>">Зарегистрируйтесь</a>.</p>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
