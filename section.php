<?php
require __DIR__ . '/includes/bootstrap.php';
$code = (string) ($_GET['s'] ?? '');
$sections = sections();
if (!isset($sections[$code])) {
    require __DIR__ . '/404.php';
    exit;
}
$sec = $sections[$code];
$items = articles($code);
$title = $sec['title'];
$description = $sec['description'];
$crumbs = [[$sec['title']]];
require __DIR__ . '/includes/header.php';
?>
<h1><?= e($sec['title']) ?></h1>
<p class="lead"><?= e($sec['description']) ?></p>
<div class="cards<?= $code === 'masters' ? ' cards-masters' : '' ?>">
    <?php foreach ($items as $a): ?>
        <article class="card">
            <a href="<?= e(article_url($a)) ?>"><?= img($a['image'], $a['title'], $code === 'masters' ? 'avatar-lg' : '') ?></a>
            <div class="card-body">
                <?php if (in_array($code, ['news', 'blog', 'promo'], true)): ?>
                    <time datetime="<?= e(substr($a['created_at'], 0, 10)) ?>"><?= e(ru_date($a['created_at'])) ?></time>
                <?php endif; ?>
                <h2 class="h3"><a href="<?= e(article_url($a)) ?>"><?= e($a['title']) ?></a></h2>
                <p><?= e($a['summary']) ?></p>
                <?php if ($a['price']): ?><p class="price">от <?= money((int) $a['price']) ?> · <?= (int) $a['duration'] ?> мин</p><?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
