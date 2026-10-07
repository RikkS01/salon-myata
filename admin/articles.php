<?php
require __DIR__ . '/../includes/bootstrap.php';
require_role('admin', 'manager');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'toggle') {
        db()->prepare('UPDATE articles SET is_published = 1 - is_published WHERE id = ?')->execute([$id]);
        flash('Статус публикации изменён.');
    } elseif (($_POST['action'] ?? '') === 'delete') {
        try {
            db()->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);
            flash('Материал удалён.');
        } catch (PDOException $e) {
            flash('Нельзя удалить: на услугу или мастера есть записи клиентов. Снимите материал с публикации.', 'error');
        }
    }
    redirect('admin/articles.php?s=' . urlencode((string) ($_POST['s'] ?? '')));
}
$s = (string) ($_GET['s'] ?? '');
$sql = 'SELECT a.*, sc.title st FROM articles a JOIN sections sc ON sc.code = a.section';
$st = $s !== '' ? db()->prepare("$sql WHERE a.section = ? ORDER BY sc.sort, a.id") : db()->prepare("$sql ORDER BY sc.sort, a.id");
$st->execute($s !== '' ? [$s] : []);
$title = 'Контент';
$crumbs = [['Панель управления', url('admin/index.php')], ['Контент']];
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_nav.php';
?>
<h1>Управление контентом</h1>
<p><a class="btn" href="<?= e(url('admin/edit.php?s=' . urlencode($s ?: 'news'))) ?>">+ Добавить материал</a>
   Раздел: <a href="?">все</a><?php foreach (sections() as $code => $sec): ?> · <a href="?s=<?= e($code) ?>"<?= $s === $code ? ' class="active"' : '' ?>><?= e($sec['title']) ?></a><?php endforeach; ?></p>
<table class="data">
    <thead><tr><th>ID</th><th>Раздел</th><th>Заголовок</th><th>Дата</th><th>Просм.</th><th>Статус</th><th>Действия</th></tr></thead>
    <tbody>
    <?php foreach ($st as $a): ?>
        <tr><td><?= (int) $a['id'] ?></td><td><?= e($a['st']) ?></td>
            <td><a href="<?= e(article_url($a)) ?>"><?= e($a['title']) ?></a></td>
            <td><?= e(substr($a['created_at'], 0, 10)) ?></td><td><?= (int) $a['views'] ?></td>
            <td><?= $a['is_published'] ? 'опубликован' : '<em>скрыт</em>' ?></td>
            <td class="actions"><a href="<?= e(url('admin/edit.php?id=' . $a['id'])) ?>">Изменить</a>
                <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $a['id'] ?>"><input type="hidden" name="s" value="<?= e($s) ?>">
                    <button class="link" name="action" value="toggle"><?= $a['is_published'] ? 'Скрыть' : 'Опубликовать' ?></button>
                    <button class="link danger" name="action" value="delete" onclick="return confirm('Удалить материал?')">Удалить</button></form></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
