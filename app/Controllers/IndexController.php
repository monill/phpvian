<?php

namespace PHPvian\Controllers;

use PHPvian\Libs\Session;

class IndexController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // Checks if the file 'config/database.php' exists
        if (!connection_file()) {
            redirect('/installer');
        } else {
            return view('index/index');
        }
    }
}
