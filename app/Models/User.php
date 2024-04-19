<?php

namespace PHPvian\Models;

use PHPvian\Libs\Database;

class User
{
    private $db;
    public $id, $username, $email, $password, $status, $banned = null;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function findByEmail($email)
    {
        // 'id, email, username, password, access',



//        $this->id = $sql->id;
//        $this->username = $sql->username;
//        $this->email = $sql->email;
//        $this->password = $sql->password;
//        $this->status = $sql->access == 1 ? true : false;
//        $this->banned = $sql->access == 1 ? true : false;
//        return $this;
    }

    public function existsByEmail($email)
    {
        if ($this->db->exists('users', 'email', $email)) {
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