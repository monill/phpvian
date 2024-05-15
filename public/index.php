<?php

use PHPvian\Libs\Session;

header("Content-Type: text/html; charset=UTF-8");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

ob_start();

if (file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php')) {
    require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
}

Session::startSession();

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app/routes.php';

// Define o caminho do arquivo de log de erros
$logFilePath = __DIR__ . "/../storage/logs/php-error.txt";
ini_set("error_log", $logFilePath);

// Configurações para exibir e registrar todos os tipos de erros
error_reporting(E_ALL); // ^ E_NOTICE
ini_set("display_errors", true);
ini_set("log_errors", true);

