<?php
require __DIR__ . '/includes/bootstrap.php';
$title = 'Карта сайта';
$crumbs = [['Карта сайта']];
require __DIR__ . '/includes/header.php';
?>
<h1>Карта сайта</h1>
<ul class="sitemap">
    <li><a href="<?= e(url()) ?>">Главная</a></li>
    <li><a href="<?= e(url('about.php')) ?>">О салоне</a></li>
    <?php foreach (sections() as $code => $s): ?>
        <li><a href="<?= e(url('section.php?s=' . $code)) ?>"><?= e($s['title']) ?></a>
            <ul><?php foreach (articles($code) as $a): ?><li><a href="<?= e(article_url($a)) ?>"><?= e($a['title']) ?></a></li><?php endforeach; ?></ul>
        </li>
    <?php endforeach; ?>
    <li><a href="<?= e(url('prices.php')) ?>">Цены</a></li>
    <li><a href="<?= e(url('booking.php')) ?>">Онлайн-запись</a></li>
    <li><a href="<?= e(url('contacts.php')) ?>">Контакты</a></li>
    <li><a href="<?= e(url('search.php')) ?>">Поиск</a></li>
    <li>Личный кабинет: <a href="<?= e(url('login.php')) ?>">вход</a>, <a href="<?= e(url('register.php')) ?>">регистрация</a></li>
</ul>
<p class="small">Для поисковых систем: <a href="<?= e(url('sitemap.xml.php')) ?>">sitemap.xml</a></p>
<?php require __DIR__ . '/includes/footer.php'; ?>
