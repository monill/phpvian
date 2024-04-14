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
//            redirect('/');
        }
    }

    protected function isUserLoggedIn()
    {
        if (Session::get("userID") || Session::get("loggedIN")) {
            if ($this->loginFingerPrint) {
                $loginString = $this->generateLoginString();
                $storedString = Session::get("login_fingerprint");

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

    protected function generateLoginString()
    {
        return hash("sha512", get_ip() . browser());
    }
}
