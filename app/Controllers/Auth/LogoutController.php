<?php

namespace PHPvian\Controllers\Auth;

use PHPvian\Libs\Session;

class LogoutController
{
    public function logout()
    {
        Session::destroySession();
//        redirect('/login');
    }
}