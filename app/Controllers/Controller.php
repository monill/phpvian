<?php

namespace PHPvian\Controllers;

use PHPvian\Libs\Connection;
use PHPvian\Libs\Session;

class Controller
{
    protected $loginFingerPrint;
    protected $conn;

    public function __construct()
    {
        $this->loginFingerPrint = config('login', 'login_fingerprint');
        $this->conn = new Connection();
    }

    protected function checkLoggedIn()
    {
        if (!$this->isUserLoggedIn()) {
//            Session::destroySession();
            redirect('/login');
        }
    }

    protected function isUserLoggedIn()
    {
        // Checks if the user is logged in
        $loggedIn = Session::get("userID") || Session::get("loggedIN");

        // If the user is logged in and fingerprint login is enabled,
        // checks if the stored fingerprint matches the currently generated one
        if ($loggedIn && $this->loginFingerPrint) {
            $storedLoginString = Session::get("loginFingerPrint");
            return ($storedLoginString !== null && $storedLoginString === generate_login_string());
        }

        return $loggedIn;
    }

}
