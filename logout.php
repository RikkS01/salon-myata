<?php
require __DIR__ . '/includes/bootstrap.php';
$_SESSION = [];
session_destroy();
session_start();
flash('Вы вышли из учётной записи.', 'info');
redirect('index.php');
