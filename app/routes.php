<?php

use PHPvian\Controllers\Auth\LoginController;
use PHPvian\Controllers\Auth\LogoutController;
use PHPvian\Controllers\Auth\SignupController;
use PHPvian\Controllers\ErrorsController;
use PHPvian\Controllers\IndexController;
use PHPvian\Controllers\InstallController;
use PHPvian\Libs\Route;

$route = new Route();

$route->get('/', IndexController::class, 'index');
//Installer
$route->get('/installer', InstallController::class, 'index');
$route->get('/installer/requirements', InstallController::class, 'requirements');
$route->get('/installer/files', InstallController::class, 'files');
$route->get('/installer/database', InstallController::class, 'database');
$route->post('/installer/database', InstallController::class, 'postDatabase');
$route->get('/installer/import', InstallController::class, 'import');
$route->post('/installer/import', InstallController::class, 'importDatabase');
$route->get('/installer/config', InstallController::class, 'config');
$route->post('/installer/config', InstallController::class, 'postConfig');
//End installer
$route->get('/404', ErrorsController::class, 'deny');
$route->get('/500', ErrorsController::class, 'server');
//Auth
$route->get('/login', LoginController::class, 'index');
$route->post('/login', LoginController::class, 'login');
$route->get('/signup', SignupController::class, 'index');
$route->post('/signup', SignupController::class, 'signup');
$route->get('/logout', LogoutController::class, 'logout');
//End Auth
$route->get('/village', \PHPvian\Controllers\VillageController::class, 'index');



try {
    $route->dispatch();
} catch (\Exception $e) {
    echo $e->getMessage();
}
