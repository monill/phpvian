<?php

namespace PHPvian\Libs;

class Validate
{
    private $conn;

    public function __construct()
    {
        $this->conn = new Connection();
    }

    /**
     * Checks if a value is empty
     */
    public function isEmpty($data)
    {
        if (is_array($data)) {
            return empty($data);
        } elseif ($data === '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validates an email address
     */
    public function validEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Checks if a username already exists
     */
    public function userExist($username)
    {
        return $this->exist('users', 'username', $username);
    }

    /**
     * Checks if an email address already exists
     */
    public function emailExist($email)
    {
        return $this->exist('users', 'email', $email);
    }

    /**
     * Checks whether an activation key is valid
     */
    public function validKey($key)
    {
        if (strlen($key) != 40) {
            return false;
        }

        $result = $this->conn->select('users', 'activation_code = :code', [':code' => $key]);

        return count($result) === 1 && $result->codeactivation !== null;
    }

    /**
     * Checks if a record exists in the table
     */
    private function exist($table, $column, $value)
    {
        return $this->conn->exists($table, $column, $value);
    }
}
