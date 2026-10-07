<?php
require __DIR__ . '/includes/bootstrap.php';
$user = current_user();
$services = articles('services');
$masters = articles('masters');
$times = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
$form = [
    'name' => $user['name'] ?? '', 'phone' => $user['phone'] ?? '',
    'service_id' => (string) ($_GET['service'] ?? ''), 'master_id' => (string) ($_GET['master'] ?? ''),
    'visit_date' => date('Y-m-d', strtotime('+1 day')), 'visit_time' => '11:00', 'comment' => '',
];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($form as $k => $_) {
        $form[$k] = trim((string) ($_POST[$k] ?? ''));
    }
    $serviceIds = array_column($services, 'id');
    $masterIds = array_column($masters, 'id');
    if (mb_strlen($form['name']) < 2) $errors[] = 'Укажите имя.';
    if (!preg_match('/^\+?[\d\s()\-]{10,20}$/', $form['phone'])) $errors[] = 'Укажите телефон в формате +7 (900) 123-45-67.';
    if (!in_array((int) $form['service_id'], $serviceIds, true)) $errors[] = 'Выберите услугу.';
    if ($form['master_id'] !== '' && !in_array((int) $form['master_id'], $masterIds, true)) $errors[] = 'Выберите мастера из списка.';
    $d = DateTime::createFromFormat('Y-m-d', $form['visit_date']);
    if (!$d || $form['visit_date'] < date('Y-m-d') || $form['visit_date'] > date('Y-m-d', strtotime('+60 days'))) {
        $errors[] = 'Дата визита – от сегодняшнего дня до 60 дней вперёд.';
    }
    if (!in_array($form['visit_time'], $times, true)) $errors[] = 'Выберите время из списка.';
    if (!$errors && $form['master_id'] !== '') {
        $busy = db()->prepare("SELECT COUNT(*) FROM bookings WHERE master_id = ? AND visit_date = ? AND visit_time = ? AND status IN ('new','confirmed')");
        $busy->execute([$form['master_id'], $form['visit_date'], $form['visit_time']]);
        if ($busy->fetchColumn() > 0) $errors[] = 'Это время у выбранного мастера уже занято. Выберите другое.';
    }
    if (!$errors) {
        db()->prepare('INSERT INTO bookings (user_id, name, phone, service_id, master_id, visit_date, visit_time, comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$user['id'] ?? null, $form['name'], $form['phone'], (int) $form['service_id'],
                $form['master_id'] === '' ? null : (int) $form['master_id'], $form['visit_date'], $form['visit_time'],
                mb_substr($form['comment'], 0, 500)]);
        flash('Заявка принята! Администратор подтвердит запись по телефону.' . ($user ? ' Статус записи – в личном кабинете.' : ''));
        redirect($user ? 'cabinet.php' : 'booking.php');
    }
}
$title = 'Онлайн-запись';
$crumbs = [['Онлайн-запись']];
require __DIR__ . '/includes/header.php';
?>
<h1>Онлайн-запись</h1>
<p class="lead">Заполните форму – администратор салона подтвердит запись в течение часа в рабочее время.
<?php if (!$user): ?> <a href="<?= e(url('login.php?next=' . urlencode(url('booking.php')))) ?>">Войдите</a>, чтобы видеть историю записей.<?php endif; ?></p>
<?php if ($errors): ?><div class="alert alert-error" role="alert"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
<form method="post" class="form form-wide">
    <?= csrf_field() ?>
    <label>Ваше имя <input name="name" required value="<?= e($form['name']) ?>"></label>
    <label>Телефон <input name="phone" type="tel" required placeholder="+7 (900) 123-45-67" value="<?= e($form['phone']) ?>"></label>
    <label>Услуга
        <select name="service_id" required><option value="">— выберите —</option>
            <?php foreach ($services as $s): ?><option value="<?= (int) $s['id'] ?>"<?= (string) $s['id'] === $form['service_id'] ? ' selected' : '' ?>><?= e($s['title']) ?> (от <?= money((int) $s['price']) ?>)</option><?php endforeach; ?>
        </select></label>
    <label>Мастер
        <select name="master_id"><option value="">Любой свободный мастер</option>
            <?php foreach ($masters as $m): ?><option value="<?= (int) $m['id'] ?>"<?= (string) $m['id'] === $form['master_id'] ? ' selected' : '' ?>><?= e($m['title']) ?></option><?php endforeach; ?>
        </select></label>
    <label>Дата <input type="date" name="visit_date" required min="<?= date('Y-m-d') ?>" value="<?= e($form['visit_date']) ?>"></label>
    <label>Время
        <select name="visit_time"><?php foreach ($times as $t): ?><option<?= $t === $form['visit_time'] ? ' selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></label>
    <label class="full">Комментарий <textarea name="comment" rows="3" placeholder="Например: первое посещение"><?= e($form['comment']) ?></textarea></label>
    <p class="full small">Нажимая «Записаться», вы соглашаетесь на обработку персональных данных (152-ФЗ) для связи по записи.</p>
    <button class="btn" type="submit">Записаться</button>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
