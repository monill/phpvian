<?php

namespace PHPvian\Models;

use PHPvian\Libs\Connection;

class Model
{
    public $conn;

    public function __construct()
    {
        $this->conn = new Connection();
    }
}