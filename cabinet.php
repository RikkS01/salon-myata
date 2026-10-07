<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'cancel') {
        db()->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'new'")
            ->execute([(int) $_POST['id'], $user['id']]);
        flash('Запись отменена.', 'info');
    } elseif (($_POST['action'] ?? '') === 'message') {
        $subject = trim((string) $_POST['subject']);
        $body = trim((string) $_POST['body']);
        if ($subject !== '' && mb_strlen($body) >= 10) {
            db()->prepare('INSERT INTO messages (user_id, name, email, subject, body) VALUES (?, ?, ?, ?, ?)')
                ->execute([$user['id'], $user['name'], $user['email'], mb_substr($subject, 0, 150), $body]);
            flash('Сообщение отправлено администратору салона.');
        } else {
            flash('Заполните тему и текст сообщения (не менее 10 символов).', 'error');
        }
    } elseif (($_POST['action'] ?? '') === 'profile') {
        $phone = trim((string) $_POST['phone']);
        db()->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?')
            ->execute([trim((string) $_POST['name']) ?: $user['name'], $phone, $user['id']]);
        flash('Профиль сохранён.');
    }
    redirect('cabinet.php');
}
$b = db()->prepare('SELECT b.*, s.title service, m.title master FROM bookings b JOIN articles s ON s.id = b.service_id
    LEFT JOIN articles m ON m.id = b.master_id WHERE b.user_id = ? ORDER BY b.visit_date DESC, b.visit_time DESC');
$b->execute([$user['id']]);
$m = db()->prepare('SELECT * FROM messages WHERE user_id = ? ORDER BY created_at DESC');
$m->execute([$user['id']]);
$statuses = ['new' => 'Ожидает подтверждения', 'confirmed' => 'Подтверждена', 'done' => 'Выполнена', 'cancelled' => 'Отменена'];
$title = 'Личный кабинет';
$crumbs = [['Личный кабинет']];
require __DIR__ . '/includes/header.php';
?>
<h1>Личный кабинет</h1>
<p>Вы вошли как <strong><?= e($user['login']) ?></strong> (<?= e(ROLES[$user['role']]) ?>).
<?php if ($user['role'] !== 'client'): ?> <a href="<?= e(url('admin/index.php')) ?>">Перейти в панель управления</a><?php endif; ?></p>
<section>
    <h2>Мои записи</h2>
    <table class="data">
        <thead><tr><th>Дата и время</th><th>Услуга</th><th>Мастер</th><th>Статус</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($b as $r): ?>
            <tr><td><?= e(ru_date($r['visit_date'])) ?>, <?= e(substr($r['visit_time'], 0, 5)) ?></td>
                <td><?= e($r['service']) ?></td><td><?= e($r['master'] ?? 'любой') ?></td>
                <td><span class="status status-<?= e($r['status']) ?>"><?= e($statuses[$r['status']]) ?></span></td>
                <td><?php if ($r['status'] === 'new'): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="link">Отменить</button></form><?php endif; ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a class="btn" href="<?= e(url('booking.php')) ?>">Новая запись</a>
</section>
<section class="two-cols">
    <div>
        <h2>Сообщения</h2>
        <?php foreach ($m as $msg): ?>
            <div class="msg">
                <p class="meta"><?= e(ru_date($msg['created_at'])) ?> · <strong><?= e($msg['subject']) ?></strong></p>
                <p><?= nl2br(e($msg['body'])) ?></p>
                <?php if ($msg['reply']): ?><div class="reply"><strong>Ответ салона:</strong> <?= nl2br(e($msg['reply'])) ?></div>
                <?php else: ?><p class="small">Ожидает ответа</p><?php endif; ?>
            </div>
        <?php endforeach; ?>
        <form method="post" class="form">
            <?= csrf_field() ?><input type="hidden" name="action" value="message">
            <label>Тема <input name="subject" required maxlength="150"></label>
            <label>Сообщение <textarea name="body" rows="4" required minlength="10"></textarea></label>
            <button class="btn" type="submit">Отправить сообщение</button>
        </form>
    </div>
    <div>
        <h2>Профиль</h2>
        <form method="post" class="form">
            <?= csrf_field() ?><input type="hidden" name="action" value="profile">
            <label>Имя <input name="name" value="<?= e($user['name']) ?>"></label>
            <label>Телефон <input name="phone" value="<?= e($user['phone']) ?>"></label>
            <label>E-mail <input value="<?= e($user['email']) ?>" disabled></label>
            <button class="btn btn-light" type="submit">Сохранить</button>
        </form>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
