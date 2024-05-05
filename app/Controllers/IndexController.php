<?php

namespace PHPvian\Controllers;

class IndexController
{
    public function index()
    {
        // Checks if the file 'database.php' exists
        if (!file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . '../config/database.php')) {
            redirect('/installer');
        } else {
            return view('index/index');
        }
    }
}
