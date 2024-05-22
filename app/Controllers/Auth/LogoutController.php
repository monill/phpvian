<?php

namespace PHPvian\Controllers\Auth;

use PHPvian\Libs\Auth;
use PHPvian\Libs\Database;
use PHPvian\Libs\Session;

class LogoutController
{
    private $database;

    public function __construct()
    {
        $this->database = new Database();
    }

    public function logout()
    {
        $this->database->activeModify(Session::get('username'), 1);
        (new Auth())->logout();
    }
}