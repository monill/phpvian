<?php

namespace PHPvian\Controllers\Auth;

use PHPvian\Controllers\Controller;
use PHPvian\Libs\Mail;
use PHPvian\Libs\Session;
use PHPvian\Libs\Validate;

class SignupController extends Controller
{
    private $mailer;
    private $valid;

    public function __construct()
    {
        parent::__construct();
        $this->mailer = new Mail();
        $this->valid = new Validate();
    }

    public function index()
    {
        return view('auth/signup');
    }

    public function signup()
    {
        if (!input_exists()) {
            redirect('/signup');
        }

        $username = input("username");
        $email = input("email");
        $password = input("password");
        $password2 = input("password2");

        $errors = $this->validSignUp($username, $email, $password, $password2);

        if (count($errors) == 0) {
            $key = md5_gen();

            try {
                $this->conn->insert('users', [
                    'username' => $username,
                    'email' => $email,
                    'password' => md5($password),
                    'codeactivation' => $key,
                    'ip' => get_ip(),
                ]);
            } catch (\Exception $exc) {
//                Session::flash("info", "There was an error creating your account.");
            }

            $this->mailer->confirmEmail($email, $key);

            echo json_encode(["status" => "success", "msg" => "Account created successfully check your email to activate your account, Inbox or SPAM."]);

            redirect('/login');
        } else {
            view('auth/signup', ['errors' => $errors]);
        }
    }

    protected function validSignUp($username, $email, $password, $password2)
    {
        $errors = array();

        if ($this->valid->isEmpty($username)) {
            $errors[] = "Please enter the account.";
        }
        if ($this->valid->isEmpty($email)) {
            $errors[] = "Please enter an email.";
        }
        if ($this->valid->isEmpty($password)) {
            $errors[] = "Please enter a password.";
        }
        if ($this->valid->isEmpty($password2)) {
            $errors[] = "Please enter the second password.";
        }
        if ($password != $password2) {
            $errors[] = "The passwords do not match.";
        }
        if (strlen($password) < 6 || strlen($password2) > 12) {
            $errors[] = "Password must be between 6 and 12 characters long.";
        }
//        if ($this->valid->validEmail($email)) {
//            $errors[] = "Please enter a valid email address.";
//        }
        if ($this->valid->emailExist($email)) {
            $errors[] = "The email provided is already in use.";
        }
        if ($this->valid->userExist($username)) {
            $errors[] = "The chosen username is already in use.";
        }
        if (strlen($username) < 3 && strlen($username) > 25) {
            $errors[] = "User can have between 3 and 25 characters.";
        }
        if (!ctype_alnum($username)) {
            $errors[] = "The username can only contain letters and numbers with no space.";
        }
        return $errors;
    }

}