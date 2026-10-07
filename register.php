<?php
/** Сценарий создания учётной записи клиента (роль client назначается автоматически). */
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) {
    redirect('cabinet.php');
}
$form = ['login' => '', 'name' => '', 'email' => '', 'phone' => ''];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($form as $k => $_) {
        $form[$k] = trim((string) ($_POST[$k] ?? ''));
    }
    $pass = (string) ($_POST['password'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_.-]{3,40}$/', $form['login'])) $errors[] = 'Логин: 3–40 латинских букв, цифр или знаков _ . -';
    if (mb_strlen($form['name']) < 2) $errors[] = 'Укажите имя.';
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Укажите корректный e-mail.';
    if ($form['phone'] !== '' && !preg_match('/^\+?[\d\s()\-]{10,20}$/', $form['phone'])) $errors[] = 'Телефон указан неверно.';
    if (strlen($pass) < 8 || !preg_match('/\d/', $pass) || !preg_match('/[a-zа-я]/iu', $pass)) {
        $errors[] = 'Пароль: не менее 8 символов, буквы и цифры.';
    }
    if ($pass !== ($_POST['password2'] ?? '')) $errors[] = 'Пароли не совпадают.';
    if (empty($_POST['agree'])) $errors[] = 'Необходимо согласие на обработку персональных данных.';
    if (!$errors) {
        $st = db()->prepare('SELECT COUNT(*) FROM users WHERE login = ? OR email = ?');
        $st->execute([$form['login'], $form['email']]);
        if ($st->fetchColumn() > 0) $errors[] = 'Пользователь с таким логином или e-mail уже существует.';
    }
    if (!$errors) {
        db()->prepare("INSERT INTO users (login, password_hash, name, email, phone, role) VALUES (?, ?, ?, ?, ?, 'client')")
            ->execute([$form['login'], password_hash($pass, PASSWORD_DEFAULT), $form['name'], $form['email'], $form['phone']]);
        login_user($form['login'], $pass);
        flash('Учётная запись создана. Добро пожаловать!');
        redirect('cabinet.php');
    }
}
$title = 'Регистрация';
$crumbs = [['Регистрация']];
require __DIR__ . '/includes/header.php';
?>
<h1>Регистрация</h1>
<p class="lead">После регистрации в личном кабинете доступны история записей и переписка с салоном.</p>
<?php if ($errors): ?><div class="alert alert-error" role="alert"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
<form method="post" class="form form-narrow">
    <?= csrf_field() ?>
    <label>Логин <input name="login" required pattern="[a-zA-Z0-9_.\-]{3,40}" value="<?= e($form['login']) ?>"></label>
    <label>Имя <input name="name" required value="<?= e($form['name']) ?>"></label>
    <label>E-mail <input type="email" name="email" required value="<?= e($form['email']) ?>"></label>
    <label>Телефон <input type="tel" name="phone" placeholder="+7 (900) 123-45-67" value="<?= e($form['phone']) ?>"></label>
    <label>Пароль <input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
    <label>Повтор пароля <input type="password" name="password2" required minlength="8" autocomplete="new-password"></label>
    <label class="check"><input type="checkbox" name="agree" value="1"> Согласен(на) на обработку персональных данных</label>
    <button class="btn" type="submit">Зарегистрироваться</button>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
