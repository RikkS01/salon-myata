<?php
require __DIR__ . '/../includes/bootstrap.php';
require_role('admin', 'manager');
$statuses = ['new' => 'Новая', 'confirmed' => 'Подтверждена', 'done' => 'Выполнена', 'cancelled' => 'Отменена'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($statuses[$_POST['status'] ?? ''])) {
        db()->prepare('UPDATE bookings SET status = ? WHERE id = ?')->execute([$_POST['status'], (int) $_POST['id']]);
        flash('Статус записи изменён.');
    }
    redirect('admin/bookings.php');
}
$rows = db()->query('SELECT b.*, s.title service, m.title master FROM bookings b JOIN articles s ON s.id = b.service_id
    LEFT JOIN articles m ON m.id = b.master_id ORDER BY b.status = "new" DESC, b.visit_date, b.visit_time')->fetchAll();
$title = 'Записи клиентов';
$crumbs = [['Панель управления', url('admin/index.php')], ['Записи']];
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_nav.php';
?>
<h1>Записи клиентов</h1>
<table class="data">
    <thead><tr><th>№</th><th>Дата, время</th><th>Клиент</th><th>Телефон</th><th>Услуга</th><th>Мастер</th><th>Комментарий</th><th>Статус</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr><td><?= (int) $r['id'] ?></td><td><?= e($r['visit_date']) ?> <?= e(substr($r['visit_time'], 0, 5)) ?></td>
            <td><?= e($r['name']) ?><?= $r['user_id'] ? ' <span class="tag">с сайта</span>' : '' ?></td><td><?= e($r['phone']) ?></td>
            <td><?= e($r['service']) ?></td><td><?= e($r['master'] ?? 'любой') ?></td><td><?= e($r['comment']) ?></td>
            <td><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <select name="status" onchange="this.form.submit()" aria-label="Статус записи <?= (int) $r['id'] ?>">
                    <?php foreach ($statuses as $k => $l): ?><option value="<?= $k ?>"<?= $k === $r['status'] ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
                </select><noscript><button>OK</button></noscript></form></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
