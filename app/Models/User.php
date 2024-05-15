<?php

namespace PHPvian\Models;

use PHPvian\Libs\Connection;

class User
{
	protected $table = 'users';
	
    private $conn, $password, $id;
    public $username, $email, $status, $banned;

    public function __construct()
    {
        $this->conn = new Connection();
    }

    public function findByEmail($email)
    {
    }

    public function existsByEmail($email)
    {
        if ($this->conn->exists('users', 'email', $email)) {
            return true;
        }
        return false;
    }

    public function isActive()
    {
        return $this->status;
    }

    public function isBanned()
    {
        return $this->banned;
    }
}