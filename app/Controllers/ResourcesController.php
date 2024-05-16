<?php

namespace PHPvian\Controllers;

class ResourcesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        echo "Resources Controller";
    }
}