<?php
/** Создание и редактирование материала любого раздела, загрузка изображения. */
require __DIR__ . '/../includes/bootstrap.php';
require_role('admin', 'manager');
$id = (int) ($_GET['id'] ?? 0);
$a = ['section' => (string) ($_GET['s'] ?? 'news'), 'slug' => '', 'title' => '', 'summary' => '', 'body' => '',
    'image' => '', 'price' => '', 'duration' => '', 'is_published' => 1];
if ($id) {
    $st = db()->prepare('SELECT * FROM articles WHERE id = ?');
    $st->execute([$id]);
    $a = $st->fetch() ?: exit('Материал не найден');
}
$errors = [];

function translit(string $s): string
{
    $map = ['а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y',
        'к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f',
        'х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'shch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya'];
    $s = strtr(mb_strtolower($s), $map);
    return trim(preg_replace('/[^a-z0-9]+/', '-', $s), '-') ?: 'material';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['section', 'slug', 'title', 'summary', 'body', 'price', 'duration'] as $k) {
        $a[$k] = trim((string) ($_POST[$k] ?? ''));
    }
    $a['is_published'] = isset($_POST['is_published']) ? 1 : 0;
    if (!isset(sections()[$a['section']])) $errors[] = 'Неизвестный раздел.';
    if (mb_strlen($a['title']) < 3) $errors[] = 'Заголовок слишком короткий.';
    if ($a['summary'] === '') $errors[] = 'Заполните краткое описание.';
    if (mb_strlen(strip_tags($a['body'])) < 50) $errors[] = 'Текст материала – не менее 50 символов.';
    $a['slug'] = $a['slug'] !== '' ? translit($a['slug']) : translit($a['title']);
    if (!empty($_FILES['image']['name'])) {
        $f = $_FILES['image'];
        $ext = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'][mime_content_type($f['tmp_name'])] ?? null;
        if ($f['error'] !== UPLOAD_ERR_OK || !$ext || $f['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Изображение: PNG, JPG или WEBP до 2 МБ.';
        } else {
            $name = 'upload-' . bin2hex(random_bytes(6)) . '.' . $ext;
            move_uploaded_file($f['tmp_name'], __DIR__ . '/../uploads/' . $name);
            $a['image'] = $name;
        }
    }
    if (!$errors) {
        $data = [$a['section'], $a['slug'], $a['title'], $a['summary'], $a['body'], $a['image'],
            $a['price'] === '' ? null : (int) $a['price'], $a['duration'] === '' ? null : (int) $a['duration'], $a['is_published']];
        try {
            if ($id) {
                db()->prepare('UPDATE articles SET section=?, slug=?, title=?, summary=?, body=?, image=?, price=?, duration=?, is_published=?, updated_at=NOW() WHERE id=?')
                    ->execute([...$data, $id]);
            } else {
                db()->prepare('INSERT INTO articles (section, slug, title, summary, body, image, price, duration, is_published) VALUES (?,?,?,?,?,?,?,?,?)')
                    ->execute($data);
            }
            flash('Материал сохранён.');
            redirect('admin/articles.php?s=' . urlencode($a['section']));
        } catch (PDOException $e) {
            $errors[] = 'Адрес (slug) уже используется в этом разделе.';
        }
    }
}
$title = $id ? 'Редактирование' : 'Новый материал';
$crumbs = [['Панель управления', url('admin/index.php')], ['Контент', url('admin/articles.php')], [$title]];
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_nav.php';
?>
<h1><?= e($title) ?></h1>
<?php if ($errors): ?><div class="alert alert-error" role="alert"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form form-wide">
    <?= csrf_field() ?>
    <label>Раздел <select name="section"><?php foreach (sections() as $code => $s): ?><option value="<?= e($code) ?>"<?= $code === $a['section'] ? ' selected' : '' ?>><?= e($s['title']) ?></option><?php endforeach; ?></select></label>
    <label>Адрес страницы (slug) <input name="slug" value="<?= e($a['slug']) ?>" placeholder="формируется из заголовка"></label>
    <label class="full">Заголовок <input name="title" required value="<?= e($a['title']) ?>"></label>
    <label class="full">Краткое описание <input name="summary" required maxlength="400" value="<?= e($a['summary']) ?>"></label>
    <label class="full">Текст (разрешены теги p, h3, ul, ol, li, strong, em, a) <textarea name="body" rows="12" required><?= e($a['body']) ?></textarea></label>
    <label>Цена, ₽ (для услуг) <input type="number" name="price" min="0" value="<?= e((string) $a['price']) ?>"></label>
    <label>Длительность, мин <input type="number" name="duration" min="0" value="<?= e((string) $a['duration']) ?>"></label>
    <label>Изображение <input type="file" name="image" accept="image/png,image/jpeg,image/webp"></label>
    <div><?= img((string) $a['image'], 'Текущее изображение', 'thumb') ?></div>
    <label class="check"><input type="checkbox" name="is_published" value="1"<?= $a['is_published'] ? ' checked' : '' ?>> Опубликован</label>
    <button class="btn" type="submit">Сохранить</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
