<?php
/** Общая инициализация: конфигурация, сессия, БД, вспомогательные функции. */
declare(strict_types=1);

$CONFIG = require __DIR__ . '/../config.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

// Адрес корня сайта (работает и в подпапке htdocs/salon-myata, и в корне домена):
// из адреса текущего скрипта отбрасывается его путь относительно папки сайта.
$root = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));
$script = str_replace('\\', '/', (string) realpath($_SERVER['SCRIPT_FILENAME'] ?? ''));
$rel = str_starts_with($script, $root) ? substr($script, strlen($root)) : '';
$name = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
define('ROOT_URL', ($rel !== '' && str_ends_with($name, $rel)) ? substr($name, 0, -strlen($rel)) : '');

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $CONFIG['db_host'], $CONFIG['db_port'], $CONFIG['db_name']),
        $CONFIG['db_user'],
        $CONFIG['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('<h1>Нет подключения к базе данных</h1><p>Проверьте настройки в config.php '
        . 'и импортируйте database/salon_myata.sql.</p>');
}

require __DIR__ . '/functions.php';
require __DIR__ . '/auth.php';
