<?php
require __DIR__ . '/includes/bootstrap.php';
$title = 'Цены';
$crumbs = [['Цены']];
$groups = [];
foreach (db()->query('SELECT * FROM prices ORDER BY id') as $p) {
    $groups[$p['category']][] = $p;
}
require __DIR__ . '/includes/header.php';
?>
<h1>Прайс-лист</h1>
<p class="lead">Цены указаны в рублях. Окончательная стоимость окрашивания зависит от длины и густоты волос.</p>
<?php foreach ($groups as $cat => $rows): ?>
    <h2><?= e($cat) ?></h2>
    <table class="price-table">
        <thead><tr><th scope="col">Услуга</th><th scope="col">Цена</th></tr></thead>
        <tbody><?php foreach ($rows as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= money((int) $r['price']) ?></td></tr><?php endforeach; ?></tbody>
    </table>
<?php endforeach; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
