<?php
require __DIR__ . '/../includes/bootstrap.php';
require_role('admin', 'manager');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'save') {
        db()->prepare('UPDATE banners SET title = ?, text = ?, link = ?, sort = ?, is_active = ? WHERE id = ?')
            ->execute([trim((string) $_POST['title']), trim((string) $_POST['text']), trim((string) $_POST['link']),
                (int) $_POST['sort'], isset($_POST['is_active']) ? 1 : 0, (int) $_POST['id']]);
        flash('Баннер сохранён.');
    }
    redirect('admin/banners.php');
}
$rows = db()->query('SELECT * FROM banners ORDER BY sort')->fetchAll();
$title = 'Баннеры';
$crumbs = [['Панель управления', url('admin/index.php')], ['Баннеры']];
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_nav.php';
?>
<h1>Баннеры главной страницы</h1>
<?php foreach ($rows as $b): ?>
    <form method="post" class="form form-wide banner-form"><?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $b['id'] ?>"><input type="hidden" name="action" value="save">
        <div class="full"><?= img($b['image'], $b['title'], 'thumb-wide') ?></div>
        <label>Заголовок <input name="title" value="<?= e($b['title']) ?>"></label>
        <label>Текст <input name="text" value="<?= e($b['text']) ?>"></label>
        <label>Ссылка <input name="link" value="<?= e($b['link']) ?>"></label>
        <label>Порядок <input type="number" name="sort" value="<?= (int) $b['sort'] ?>"></label>
        <label class="check"><input type="checkbox" name="is_active" value="1"<?= $b['is_active'] ? ' checked' : '' ?>> Показывать</label>
        <button class="btn" type="submit">Сохранить</button>
    </form>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
