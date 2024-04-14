<?php

namespace PHPvian\Controllers;

class ErrorsController
{
    public function deny()
    {
        return view('errors/404');
    }

    public function server()
    {
        return view('errors/500');
    }
}