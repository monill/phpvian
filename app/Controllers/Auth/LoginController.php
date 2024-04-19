<?php

namespace PHPvian\Controllers\Auth;

use PHPvian\Controllers\Controller;
use PHPvian\Libs\Cookie;
use PHPvian\Libs\Session;
use PHPvian\Libs\Validate;
use PHPvian\Models\User;

class LoginController extends Controller
{
    private $validation;

    public function __construct()
    {
        parent::__construct();
        $this->validation = new Validate();
    }

    public function index()
    {
        return view('auth/login');
    }

    public function login()
    {
        $email = input('email');
        $password = input('password');

        $this->validLogin($email, $password);

        $user = new User();
        $userExists = $user->existsByEmail($email);

        if (!$userExists || md5($password) !== $user->password) {
            error_response("Invalid email or password.");
        }
        if (!$user->isActive()) {
            error_response("Account not activated.");
        }
        if ($user->isBanned()) {
            error_response("Account banned.");
        }

        Session::set("loggedIN", true);
        Session::set("userID", (int)$user->id);
        Session::set("username", $user->username);
        Cookie::set("PHPvian", $user->username, 60480);

        if ($this->loginFingerPrint) {
            Session::set("loginFingerPrint", $this->generateLoginString());
        }

        redirect("/village");
    }

    public function validLogin($email, $password)
    {
        if ($this->validation->isEmpty($email) || $this->validation->isEmpty($password)) {
            error_response("Please enter both email and password.");
        }
        if (!$this->validation->emailExist($email)) {
            error_response("Email not exists...");
        }
    }

}