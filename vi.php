<?php
/** Переключение настроек версии для слабовидящих; значения хранятся в cookie 1 год. */
require __DIR__ . '/includes/bootstrap.php';
$allowed = ['vi' => ['0', '1'], 'vi_size' => ['1', '2', '3'], 'vi_scheme' => ['bw', 'wb', 'blue', 'brown'],
    'vi_img' => ['0', '1'], 'vi_space' => ['0', '1']];
foreach ($allowed as $name => $values) {
    if (isset($_GET[$name]) && in_array($_GET[$name], $values, true)) {
        setcookie($name, $_GET[$name], ['expires' => time() + 31536000, 'path' => url(), 'samesite' => 'Lax']);
    }
}
$back = (string) ($_GET['back'] ?? '');
// возврат только на страницу этого же сайта
if ($back === '' || !str_starts_with($back, '/') || str_starts_with($back, '//')) {
    $back = url();
}
header('Location: ' . $back);
