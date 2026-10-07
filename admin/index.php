<?php
require __DIR__ . '/../includes/bootstrap.php';
$user = require_role('admin', 'manager');
$c = db()->query("SELECT
    (SELECT COUNT(*) FROM articles) articles,
    (SELECT COUNT(*) FROM bookings WHERE status = 'new') new_bookings,
    (SELECT COUNT(*) FROM bookings) bookings,
    (SELECT COUNT(*) FROM messages WHERE status = 'new') new_messages,
    (SELECT COUNT(*) FROM users) users,
    (SELECT COALESCE(SUM(views), 0) FROM articles) views")->fetch();
$top = db()->query('SELECT section, slug, title, views FROM articles ORDER BY views DESC LIMIT 5')->fetchAll();
$title = 'Панель управления';
$crumbs = [['Панель управления']];
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_nav.php';
?>
<h1>Панель управления</h1>
<p>Роль: <strong><?= e(ROLES[$user['role']]) ?></strong>.
<?= $user['role'] === 'admin' ? 'Доступны все разделы, включая управление пользователями.' : 'Доступны контент, записи, сообщения и баннеры.' ?></p>
<div class="stats">
    <a href="<?= e(url('admin/bookings.php')) ?>"><strong><?= (int) $c['new_bookings'] ?></strong> новых записей</a>
    <a href="<?= e(url('admin/messages.php')) ?>"><strong><?= (int) $c['new_messages'] ?></strong> новых сообщений</a>
    <a href="<?= e(url('admin/articles.php')) ?>"><strong><?= (int) $c['articles'] ?></strong> материалов</a>
    <span><strong><?= (int) $c['bookings'] ?></strong> записей всего</span>
    <span><strong><?= (int) $c['users'] ?></strong> пользователей</span>
    <span><strong><?= (int) $c['views'] ?></strong> просмотров</span>
</div>
<h2>Популярные материалы</h2>
<ol><?php foreach ($top as $t): ?><li><a href="<?= e(article_url($t)) ?>"><?= e($t['title']) ?></a> – <?= (int) $t['views'] ?></li><?php endforeach; ?></ol>
<?php require __DIR__ . '/../includes/footer.php'; ?>
