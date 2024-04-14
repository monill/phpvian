<?php

namespace PHPvian\Views;

use PHPvian\Exceptions\DetailableException;

class ViewFileNotFoundException extends DetailableException
{
    public function __construct()
    {
        parent::__construct("Views file not found.");
    }

    public function getDetail()
    {
        return "Make sure you have your view file located in 'resources/views'.";
    }
}