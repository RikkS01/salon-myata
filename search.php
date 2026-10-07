<?php
require __DIR__ . '/includes/bootstrap.php';
$q = trim((string) ($_GET['q'] ?? ''));
$results = [];
if (mb_strlen($q) >= 2) {
    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
    $st = db()->prepare('SELECT a.*, s.title AS section_title FROM articles a JOIN sections s ON s.code = a.section
        WHERE a.is_published = 1 AND (a.title LIKE ? OR a.summary LIKE ? OR a.body LIKE ?)
        ORDER BY (a.title LIKE ?) DESC, s.sort, a.created_at DESC LIMIT 50');
    $st->execute([$like, $like, $like, $like]);
    $results = $st->fetchAll();
}
$title = 'Поиск';
$crumbs = [['Поиск']];
require __DIR__ . '/includes/header.php';
?>
<h1>Поиск по сайту</h1>
<form class="search search-page" action="" method="get" role="search">
    <label class="sr" for="q2">Запрос</label>
    <input id="q2" type="search" name="q" value="<?= e($q) ?>" placeholder="Например: маникюр">
    <button type="submit">Найти</button>
</form>
<?php if ($q !== '' && mb_strlen($q) < 2): ?>
    <p>Введите не менее двух символов.</p>
<?php elseif ($q !== ''): ?>
    <p>По запросу «<?= e($q) ?>» найдено: <?= count($results) ?>.</p>
    <ol class="search-results">
        <?php foreach ($results as $r): ?>
            <li><a href="<?= e(article_url($r)) ?>"><?= e($r['title']) ?></a> <span class="tag"><?= e($r['section_title']) ?></span>
                <p><?= e($r['summary']) ?></p></li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
