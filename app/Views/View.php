<?php

namespace PHPvian\Views;

class View
{
    /**
     * Import a view
     */
    public function load($path, array $data)
    {
        extract($data);
        require_once "../resources/views/" . $path . ".php";
    }
}