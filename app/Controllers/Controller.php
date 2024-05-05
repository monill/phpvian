<?php

namespace PHPvian\Controllers;

use PHPvian\Libs\Connection;
use PHPvian\Libs\Session;

class Controller
{
    protected $loginFingerPrint;
    protected Connection $db;

    public function __construct()
    {
        $this->db = new Connection();
        $this->loginFingerPrint = config('login', 'login_fingerprint');
        Session::startSession();
    }

    protected function checkLoggedIn()
    {
        if (!$this->isUserLoggedIn()) {
            Session::destroySession();
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
            $currentLoginString = $this->generateLoginString();
            $storedLoginString = Session::get("loginFingerPrint");
            return ($storedLoginString !== null && $storedLoginString === $currentLoginString);
        }

        return $loggedIn;
    }

    /**
     * Generate a string that will be used as a fingerprint.
     * This is actually a string created from the user's browser name and the user's IP
     * Address, so if someone steals users session, he will not be able to access.
     */
    protected function generateLoginString()
    {
        return hash("sha512", get_ip() . browser());
    }
}
