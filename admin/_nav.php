<?php
$items = ['index.php' => 'Сводка', 'articles.php' => 'Контент', 'bookings.php' => 'Записи',
    'messages.php' => 'Сообщения', 'banners.php' => 'Баннеры'];
if (has_role('admin')) {
    $items['users.php'] = 'Пользователи';
}
$cur = basename($_SERVER['SCRIPT_NAME']);
?>
<nav class="admin-nav" aria-label="Панель управления">
    <?php foreach ($items as $f => $l): ?>
        <a href="<?= e(url('admin/' . $f)) ?>"<?= $cur === $f ? ' class="active"' : '' ?>><?= e($l) ?></a>
    <?php endforeach; ?>
</nav>
