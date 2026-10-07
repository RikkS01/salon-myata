<?php
require __DIR__ . '/includes/bootstrap.php';
$user = current_user();
$errors = [];
$form = ['name' => $user['name'] ?? '', 'email' => $user['email'] ?? '', 'subject' => '', 'body' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($form as $k => $_) {
        $form[$k] = trim((string) ($_POST[$k] ?? ''));
    }
    if (mb_strlen($form['name']) < 2) $errors[] = 'Укажите имя.';
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Укажите корректный e-mail.';
    if ($form['subject'] === '') $errors[] = 'Укажите тему сообщения.';
    if (mb_strlen($form['body']) < 10) $errors[] = 'Сообщение должно содержать не менее 10 символов.';
    if (!empty($_POST['website'])) $errors[] = 'Сообщение отклонено.'; // ловушка для спам-ботов
    if (!$errors) {
        db()->prepare('INSERT INTO messages (user_id, name, email, subject, body) VALUES (?, ?, ?, ?, ?)')
            ->execute([$user['id'] ?? null, $form['name'], $form['email'], mb_substr($form['subject'], 0, 150), $form['body']]);
        flash($user ? 'Сообщение отправлено. Ответ появится в личном кабинете.' : 'Сообщение отправлено. Мы ответим на ваш e-mail.');
        redirect('contacts.php');
    }
}
$title = 'Контакты';
$crumbs = [['Контакты']];
require __DIR__ . '/includes/header.php';
?>
<h1>Контакты</h1>
<div class="two-cols">
    <div>
        <dl class="contacts">
            <dt>Адрес</dt><dd><?= e(cfg('address')) ?></dd>
            <dt>Телефон</dt><dd><a href="tel:<?= e(preg_replace('/[^\d+]/', '', cfg('phone'))) ?>"><?= e(cfg('phone')) ?></a></dd>
            <dt>E-mail</dt><dd><a href="mailto:<?= e(cfg('email')) ?>"><?= e(cfg('email')) ?></a></dd>
            <dt>Режим работы</dt><dd><?= e(cfg('hours')) ?></dd>
            <dt>Как добраться</dt><dd>5 минут пешком от станции метро, вход со двора, без ступеней.</dd>
        </dl>
    </div>
    <div>
        <h2>Написать нам</h2>
        <?php if ($errors): ?><div class="alert alert-error" role="alert"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <label>Имя <input name="name" required value="<?= e($form['name']) ?>"></label>
            <label>E-mail <input type="email" name="email" required value="<?= e($form['email']) ?>"></label>
            <label>Тема <input name="subject" required maxlength="150" value="<?= e($form['subject']) ?>"></label>
            <label>Сообщение <textarea name="body" rows="5" required><?= e($form['body']) ?></textarea></label>
            <label class="hp">Сайт <input name="website" tabindex="-1" autocomplete="off"></label>
            <button class="btn" type="submit">Отправить</button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
