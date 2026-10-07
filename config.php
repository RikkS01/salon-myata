<?php
/**
 * Настройки сайта. Значения по умолчанию подходят для XAMPP
 * (MySQL/MariaDB: пользователь root без пароля, база salon_myata).
 * На хостинге создайте рядом файл config.local.php с теми же ключами
 * (он не попадает в репозиторий) – его значения заменят значения ниже.
 */
$config = [
    'db_host'   => 'localhost',
    'db_port'   => 3306,
    'db_name'   => 'salon_myata',
    'db_user'   => 'root',
    'db_pass'   => '',
    'site_name' => 'Салон красоты «Мята»',
    'phone'     => '+7 (495) 000-00-00',
    'email'     => 'info@myata-salon.local',
    'address'   => 'г. Москва, ул. Примерная, д. 1 (учебный адрес)',
    'hours'     => 'Ежедневно с 9:00 до 21:00',
];
if (is_file(__DIR__ . '/config.local.php')) {
    $config = array_merge($config, require __DIR__ . '/config.local.php');
}
return $config;
