<?php

namespace PHPvian\Controllers\Auth;

class SignupController
{
    public function index()
    {
        return "Signup Page";
    }

    public function signup()
    {
        if (!input_exists()) {
            redirect("/signup");
        }
    }

}