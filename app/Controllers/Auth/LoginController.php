<?php

namespace PHPvian\Controllers\Auth;

use PHPvian\Controllers\Controller;
use PHPvian\Libs\Connection;
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
        if (!input_exists()) {
            redirect('/login');
        }

        $email = input('email');
        $password = input('password');

        $errors = $this->validLogin($email, $password);
        if (count($errors) == 0) {

            $user = $this->conn->select('id, username, email, password, reg2')
                ->from('users')
                ->where('`email` = :email', [':email' => $email])
                ->first();

            if ($user) {
                Session::set('loggedIN', true);
                Session::set('userID', (int) $user['id']);
                Session::set('username', $user['username']);
                Cookie::set('uname', $user['username'], 60480);
            }

            Session::set('loginFingerPrint', generate_login_string());

            if ($user['reg2'] == 1) {
                redirect('/activate');
            } else {
                redirect('/village');
            }

        } else {
            view('auth/login', ['errors' => $errors]);
        }

//        $user = new User();
//        $userExists = $user->existsByEmail($email);
//        if (!$userExists || md5($password) !== $user->password) {
//            error_response('Invalid email or password.');
//        }
//        if (!$user->isActive()) {
//            error_response('Account not activated.');
//        }
//        if ($user->isBanned()) {
//            error_response('Account banned.');
//        }
    }

    public function validLogin($email, $password)
    {
        $errors = array();

        if ($this->validation->isEmpty($email) || $this->validation->isEmpty($password)) {
            $errors[] = 'Please enter both email and password.';
        }
        if (!$this->validation->emailExist($email)) {
            $errors[] = 'Email not exists...';
        }
        if (strlen($password) < 6 || strlen($password) > 12) {
            $errors[] = 'Password must be between 6 and 12 characters long.';
        }
        return $errors;
    }

}