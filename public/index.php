<?php

header("Content-Type: text/html; charset=UTF-8");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

ob_start();

if (file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php')) {
    require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
}

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app/routes.php';

// Set timezone
date_default_timezone_set('America/Sao_Paulo');

error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", true);
ini_set("log_errors", true);
ini_set("error_log", __DIR__ . "../storage/logs/php-error.txt");