<?php

namespace PHPvian\Exceptions;

abstract class DetailableException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
        if (PHP_SAPI === "cli") {
            echo "Detail: " . $this->getDetail() . PHP_EOL;
        }
    }
    abstract function getDetail();
}