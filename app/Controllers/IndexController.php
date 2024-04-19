<?php

namespace PHPvian\Controllers;

class IndexController extends Controller
{
    public function index()
    {
        // Checks if the file 'database.php' exists
//        if (!file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . '../config/game.php')) {
//            header("Location: " . '/installer', true);
//            exit();
//        } else {
            return view('index/index');
//        }
    }
}
