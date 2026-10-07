</main>
<footer class="site-footer">
    <div class="wrap footer-grid">
        <div>
            <strong>Салон красоты «Мята»</strong>
            <p><?= e(cfg('address')) ?><br><?= e(cfg('hours')) ?></p>
            <p><a href="tel:<?= e(preg_replace('/[^\d+]/', '', cfg('phone'))) ?>"><?= e(cfg('phone')) ?></a> ·
               <a href="mailto:<?= e(cfg('email')) ?>"><?= e(cfg('email')) ?></a></p>
        </div>
        <div>
            <strong>Разделы</strong>
            <ul>
                <?php foreach (sections() as $code => $s): ?>
                    <li><a href="<?= e(url('section.php?s=' . $code)) ?>"><?= e($s['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <strong>Клиентам</strong>
            <ul>
                <li><a href="<?= e(url('booking.php')) ?>">Онлайн-запись</a></li>
                <li><a href="<?= e(url('prices.php')) ?>">Цены</a></li>
                <li><a href="<?= e(url('contacts.php')) ?>">Написать нам</a></li>
                <li><a href="<?= e(url('sitemap.php')) ?>">Карта сайта</a></li>
            </ul>
        </div>
    </div>
    <div class="wrap footer-note">
        © 2026 Салон красоты «Мята». Учебный проект: салон, адрес и персонал вымышлены.
        Разработчик – Яцко Сорин, МУ им. С.Ю. Витте.
    </div>
</footer>
<script src="<?= e(url('assets/js/main.js')) ?>" defer></script>
</body>
</html>
