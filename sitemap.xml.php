<?php
require __DIR__ . '/includes/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
$host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$pages = ['', 'about.php', 'prices.php', 'booking.php', 'contacts.php', 'sitemap.php'];
foreach (array_keys(sections()) as $code) {
    $pages[] = 'section.php?s=' . $code;
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($pages as $p) {
    echo '  <url><loc>' . e($host . url($p)) . "</loc></url>\n";
}
foreach (db()->query('SELECT section, slug, COALESCE(updated_at, created_at) d FROM articles WHERE is_published = 1') as $a) {
    echo '  <url><loc>' . e($host . article_url($a)) . '</loc><lastmod>' . substr($a['d'], 0, 10) . "</lastmod></url>\n";
}
echo "</urlset>\n";
