<?php

namespace PHPvian;

class Autoload
{
    public function __construct()
    {
        if (file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php')) {
            require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
        }
    }

}
