<?php
require __DIR__ . '/includes/bootstrap.php';
$banners = db()->query('SELECT * FROM banners WHERE is_active = 1 ORDER BY sort')->fetchAll();
$services = articles('services', 6);
$masters = articles('masters', 3);
$news = articles('news', 3);
$title = cfg('site_name');
require __DIR__ . '/includes/header.php';
?>
<section class="slider" aria-label="Баннеры">
    <?php foreach ($banners as $i => $b): ?>
        <a class="slide<?= $i === 0 ? ' active' : '' ?>" href="<?= e(url($b['link'])) ?>"
           style="background-image:url('<?= e(url('assets/img/' . $b['image'])) ?>')">
            <span class="slide-text"><strong><?= e($b['title']) ?></strong><span><?= e($b['text']) ?></span>
                <span class="btn">Подробнее</span></span>
        </a>
    <?php endforeach; ?>
    <div class="slider-dots"><?php foreach ($banners as $i => $b): ?>
        <button type="button" aria-label="Баннер <?= $i + 1 ?>"<?= $i === 0 ? ' class="active"' : '' ?>></button><?php endforeach; ?></div>
</section>

<section class="intro">
    <h1>Салон красоты «Мята» – красота без спешки</h1>
    <p>Стрижки и окрашивание, ногтевой сервис, косметология, массаж и макияж в одном месте.
       Мастера с опытом от 5 лет, стерильные инструменты и онлайн-запись в удобное время.</p>
    <a class="btn btn-lg" href="<?= e(url('booking.php')) ?>">Записаться онлайн</a>
</section>

<section>
    <h2>Услуги</h2>
    <div class="cards">
        <?php foreach ($services as $a): ?>
            <article class="card">
                <a href="<?= e(article_url($a)) ?>"><?= img($a['image'], $a['title']) ?></a>
                <div class="card-body">
                    <h3><a href="<?= e(article_url($a)) ?>"><?= e($a['title']) ?></a></h3>
                    <p><?= e($a['summary']) ?></p>
                    <p class="price">от <?= money((int) $a['price']) ?> · <?= (int) $a['duration'] ?> мин</p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="two-cols">
    <div>
        <h2>Новости</h2>
        <?php foreach ($news as $a): ?>
            <article class="news-item">
                <time datetime="<?= e(substr($a['created_at'], 0, 10)) ?>"><?= e(ru_date($a['created_at'])) ?></time>
                <h3><a href="<?= e(article_url($a)) ?>"><?= e($a['title']) ?></a></h3>
                <p><?= e($a['summary']) ?></p>
            </article>
        <?php endforeach; ?>
        <a href="<?= e(url('section.php?s=news')) ?>">Все новости →</a>
    </div>
    <div>
        <h2>Наши мастера</h2>
        <?php foreach ($masters as $a): ?>
            <article class="master-mini">
                <?= img($a['image'], $a['title'], 'avatar') ?>
                <div><h3><a href="<?= e(article_url($a)) ?>"><?= e($a['title']) ?></a></h3>
                    <p><?= e($a['summary']) ?></p></div>
            </article>
        <?php endforeach; ?>
        <a href="<?= e(url('section.php?s=masters')) ?>">Все мастера →</a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
