<?php
/** Шапка сайта. Перед подключением можно задать $title, $description, $crumbs. */
$v = vi();
$user = current_user();
$title = $title ?? cfg('site_name');
$crumbs = $crumbs ?? [];
$current = substr($_SERVER['SCRIPT_NAME'], strlen(ROOT_URL) + 1);
$curSection = $_GET['s'] ?? '';
$menu = [
    ['index.php', 'Главная', ''], ['about.php', 'О салоне', ''],
    ['section.php', 'Услуги', 'services'], ['section.php', 'Мастера', 'masters'],
    ['prices.php', 'Цены', ''], ['section.php', 'Акции', 'promo'],
    ['section.php', 'Новости', 'news'], ['section.php', 'Блог', 'blog'],
    ['contacts.php', 'Контакты', ''],
];
$bodyClass = $v['on'] ? 'vi vi-size-' . $v['size'] . ' vi-' . $v['scheme']
    . ($v['img'] ? '' : ' vi-noimg') . ($v['space'] ? ' vi-space' : '') : '';
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title === cfg('site_name') ? $title : $title . ' – ' . cfg('site_name')) ?></title>
    <meta name="description" content="<?= e($description ?? 'Салон красоты «Мята»: стрижки, окрашивание, маникюр, косметология, массаж и макияж. Онлайн-запись.') ?>">
    <link rel="icon" href="<?= e(url('favicon.ico')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <?php if ($v['on']): ?><link rel="stylesheet" href="<?= e(url('assets/css/vi.css')) ?>"><?php endif; ?>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip" href="#main">Перейти к содержанию</a>
<?php if ($v['on']): ?>
<div class="vi-panel" role="region" aria-label="Настройки версии для слабовидящих">
    <div class="wrap vi-panel-inner">
        <span class="vi-group">Размер шрифта:
            <?= vi_link('vi_size', '1', 'A', $v['size'] === '1') ?>
            <?= vi_link('vi_size', '2', 'A+', $v['size'] === '2') ?>
            <?= vi_link('vi_size', '3', 'A++', $v['size'] === '3') ?></span>
        <span class="vi-group">Цвет:
            <?= vi_link('vi_scheme', 'bw', 'Ч/Б', $v['scheme'] === 'bw') ?>
            <?= vi_link('vi_scheme', 'wb', 'Б/Ч', $v['scheme'] === 'wb') ?>
            <?= vi_link('vi_scheme', 'blue', 'Синий', $v['scheme'] === 'blue') ?>
            <?= vi_link('vi_scheme', 'brown', 'Бежевый', $v['scheme'] === 'brown') ?></span>
        <span class="vi-group">Изображения:
            <?= vi_link('vi_img', '1', 'Вкл.', $v['img']) ?>
            <?= vi_link('vi_img', '0', 'Выкл.', !$v['img']) ?></span>
        <span class="vi-group">Интервал:
            <?= vi_link('vi_space', '0', 'Обычный', !$v['space']) ?>
            <?= vi_link('vi_space', '1', 'Большой', $v['space']) ?></span>
        <?= vi_link('vi', '0', 'Обычная версия сайта', false) ?>
    </div>
</div>
<?php endif; ?>
<header class="site-header">
    <div class="wrap header-top">
        <a class="logo" href="<?= e(url()) ?>">
            <img src="<?= e(url('assets/img/logo.png')) ?>" alt="" width="48" height="48">
            <span><strong>Мята</strong><small>салон красоты</small></span>
        </a>
        <form class="search" action="<?= e(url('search.php')) ?>" method="get" role="search">
            <label class="sr" for="q">Поиск по сайту</label>
            <input id="q" type="search" name="q" placeholder="Поиск по сайту" value="<?= e($_GET['q'] ?? '') ?>">
            <button type="submit">Найти</button>
        </form>
        <div class="header-contacts">
            <a href="tel:<?= e(preg_replace('/[^\d+]/', '', cfg('phone'))) ?>"><?= e(cfg('phone')) ?></a>
            <small><?= e(cfg('hours')) ?></small>
        </div>
        <div class="header-actions">
            <?php if (!$v['on']): ?>
                <a class="btn-vi" href="<?= e(url('vi.php?vi=1&back=' . urlencode($_SERVER['REQUEST_URI'] ?? ''))) ?>"
                   title="Версия для слабовидящих">Версия для слабовидящих</a>
            <?php endif; ?>
            <?php if ($user): ?>
                <a href="<?= e(url($user['role'] === 'client' ? 'cabinet.php' : 'admin/index.php')) ?>">
                    <?= e($user['login']) ?> (<?= e(ROLES[$user['role']]) ?>)</a> ·
                <a href="<?= e(url('logout.php')) ?>">Выйти</a>
            <?php else: ?>
                <a href="<?= e(url('login.php')) ?>">Вход</a> · <a href="<?= e(url('register.php')) ?>">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
    <nav class="main-nav" aria-label="Главное меню">
        <div class="wrap">
            <input type="checkbox" id="menu-toggle" class="menu-toggle">
            <label for="menu-toggle" class="menu-label">☰ Меню</label>
            <ul>
                <?php foreach ($menu as [$file, $label, $menuSec]):
                    $href = $menuSec ? "section.php?s=$menuSec" : $file;
                    $active = $current === $file && ($menuSec === '' || $menuSec === $curSection); ?>
                    <li><a href="<?= e(url($href)) ?>"<?= $active ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a></li>
                <?php endforeach; ?>
                <li><a class="nav-cta" href="<?= e(url('booking.php')) ?>">Записаться</a></li>
            </ul>
        </div>
    </nav>
</header>
<main id="main" class="wrap">
<?php if ($crumbs): ?>
    <nav class="crumbs" aria-label="Навигационная цепочка">
        <a href="<?= e(url()) ?>">Главная</a>
        <?php foreach ($crumbs as $crumb): ?> › <?= isset($crumb[1]) ? '<a href="' . e($crumb[1]) . '">' . e($crumb[0]) . '</a>' : '<span>' . e($crumb[0]) . '</span>' ?><?php endforeach; ?>
    </nav>
<?php endif; ?>
<?= flashes() ?>
