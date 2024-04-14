<?php

use PHPvian\Controllers\ErrorsController;
use PHPvian\Controllers\IndexController;
use PHPvian\Controllers\InstallController;
use PHPvian\Libs\Route;

$route = new Route();

$route->get('/', IndexController::class, 'index');
$route->get('/installer', InstallController::class, 'index');
$route->post('/installer/process', InstallController::class, 'process');
$route->get('/404', ErrorsController::class, 'deny');
$route->get('/500', ErrorsController::class, 'server');
$route->get('/login', \PHPvian\Controllers\Auth\LoginController::class, 'index');
$route->get('/signup', \PHPvian\Controllers\Auth\SignupController::class, 'index');
$route->get('/logout', \PHPvian\Controllers\Auth\LogoutController::class, 'logout');





try {
    $route->dispatch();
} catch (\Exception $e) {
    echo $e->getMessage();
}
