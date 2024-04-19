<?php

namespace PHPvian\Models;

use PHPvian\Libs\Database;

class Model
{
    protected $db;

    public function __construct()
    {
        $this->db = new Database();
    }
}