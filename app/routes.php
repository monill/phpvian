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
$route->get('/installer/world', InstallController::class, 'world');
$route->post('/installer/world', InstallController::class, 'createWorld');
$route->get('/installer/multihunter', InstallController::class, 'multihunter');
$route->post('/installer/multihunter', InstallController::class, 'setMultihunter');
$route->get('/installer/oasis', InstallController::class, 'oasis');
$route->post('/installer/oasis', InstallController::class, 'setOasis');
$route->get('/installer/complete', InstallController::class, 'complete');
//End installer
//Activate
$route->get('/activate', \PHPvian\Controllers\Auth\ActivateController::class, 'index');
$route->post('/activate', \PHPvian\Controllers\Auth\ActivateController::class, 'postActivate');
//Server error
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
$route->get('/resources', \PHPvian\Controllers\ResourcesController::class, 'index');



try {
    $route->dispatch();
} catch (\Exception $e) {
    echo $e->getMessage();
}
