<?php
if (!function_exists('e')) {
    require __DIR__ . '/includes/bootstrap.php';
}
http_response_code(404);
$title = 'Страница не найдена';
$crumbs = [['Ошибка 404']];
require __DIR__ . '/includes/header.php';
?>
<section class="page-404">
    <?= img('404.png', 'Ошибка 404', 'img-404') ?>
    <h1>Страница не найдена</h1>
    <p>Возможно, ссылка устарела или в адресе опечатка. Попробуйте найти нужное через поиск
       или перейдите в один из разделов:</p>
    <form class="search search-page" action="<?= e(url('search.php')) ?>" method="get" role="search">
        <label class="sr" for="q404">Поиск</label>
        <input id="q404" type="search" name="q" placeholder="Что вы искали?"><button type="submit">Найти</button>
    </form>
    <p><a class="btn" href="<?= e(url()) ?>">На главную</a> <a class="btn btn-light" href="<?= e(url('sitemap.php')) ?>">Карта сайта</a>
       <a class="btn btn-light" href="<?= e(url('booking.php')) ?>">Записаться</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
