<?php
require __DIR__ . '/includes/bootstrap.php';
$st = db()->prepare('SELECT * FROM articles WHERE section = ? AND slug = ? AND is_published = 1');
$st->execute([(string) ($_GET['s'] ?? ''), (string) ($_GET['slug'] ?? '')]);
$a = $st->fetch();
if (!$a) {
    require __DIR__ . '/404.php';
    exit;
}
db()->prepare('UPDATE articles SET views = views + 1 WHERE id = ?')->execute([$a['id']]);
$sec = sections()[$a['section']];
$title = $a['title'];
$description = $a['summary'];
$crumbs = [[$sec['title'], url('section.php?s=' . $a['section'])], [$a['title']]];
$more = db()->prepare('SELECT * FROM articles WHERE section = ? AND id <> ? AND is_published = 1 ORDER BY RAND() LIMIT 3');
$more->execute([$a['section'], $a['id']]);
require __DIR__ . '/includes/header.php';
?>
<article class="article">
    <h1><?= e($a['title']) ?></h1>
    <p class="meta"><?= e($sec['title']) ?> · <?= e(ru_date($a['created_at'])) ?> · просмотров: <?= (int) $a['views'] + 1 ?></p>
    <figure class="article-image"><?= img($a['image'], $a['title'], $a['section'] === 'masters' ? 'avatar-lg' : '') ?></figure>
    <p class="lead"><?= e($a['summary']) ?></p>
    <div class="article-body"><?= safe_html($a['body']) ?></div>
    <?php if ($a['section'] === 'services'): ?>
        <p class="price-box">Стоимость: от <?= money((int) $a['price']) ?> · Длительность: <?= (int) $a['duration'] ?> мин</p>
        <a class="btn" href="<?= e(url('booking.php?service=' . $a['id'])) ?>">Записаться на услугу</a>
    <?php elseif ($a['section'] === 'masters'): ?>
        <a class="btn" href="<?= e(url('booking.php?master=' . $a['id'])) ?>">Записаться к мастеру</a>
    <?php endif; ?>
</article>
<aside class="more">
    <h2>Ещё в разделе «<?= e($sec['title']) ?>»</h2>
    <ul><?php foreach ($more as $m): ?><li><a href="<?= e(article_url($m)) ?>"><?= e($m['title']) ?></a></li><?php endforeach; ?></ul>
</aside>
<?php require __DIR__ . '/includes/footer.php'; ?>
