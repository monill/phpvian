<?php

namespace PHPvian\Controllers;

use PHPvian\Libs\Database;
use PHPvian\Libs\Session;

class Controller
{
    protected $db, $loginFingerPrint;

    public function __construct()
    {
        $this->db = new Database();
        $this->loginFingerPrint = config('login', 'login_fingerprint');
        $this->checkLoggedIn();
    }

    protected function checkLoggedIn()
    {
        if (!$this->isUserLoggedIn()) {
//            Session::destroySession();
//            redirect("/login");
        }
    }

    protected function isUserLoggedIn()
    {
        if (Session::get("userID") || Session::get("loggedIN")) {
            if ($this->loginFingerPrint) {
                $loginString = $this->generateLoginString();
                $storedString = Session::get("loginFingerPrint");

                if ($storedString !== null && $storedString === $loginString) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
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
