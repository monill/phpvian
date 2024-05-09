<?php

namespace PHPvian\Libs;

use Exception;

class Route
{
    protected array $routes = [];

    public function get($route, $controller, $action)
    {
        $this->addRoute('GET', $route, $controller, $action);
    }

    public function post($route, $controller, $action)
    {
        $this->addRoute('POST', $route, $controller, $action);
    }

    private function addRoute($method, $route, $controller, $action)
    {
        $this->routes[$method][$route] = ['controller' => $controller, 'action' => $action];
    }

    public function dispatch()
    {
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        $method = $_SERVER['REQUEST_METHOD'];

        if (!isset($this->routes[$method])) {
            throw new Exception("Unsupported HTTP method: $method");
        }

        foreach ($this->routes[$method] as $route => $handler) {
            if ($this->routeMatches($route, $uri)) {
                $controller = $handler['controller'];
                $action = $handler['action'];

                if (!class_exists($controller)) {
                    throw new Exception("Controller class not found: $controller");
                }

                $controllerInstance = new $controller();

                if (!method_exists($controllerInstance, $action)) {
                    throw new Exception("Action method not found: $action");
                }

                $controllerInstance->$action();
                return;
            }
        }

        throw new Exception("No route found for URI: $uri");
    }

    private function routeMatches($route, $uri)
    {
        $regex = str_replace('/', '\/', $route) . '\/?';
        $regex = preg_replace('/\{(\w+)\}/', '(?<$1>\w+)', $regex);
        $regex = "/^$regex$/";

        return preg_match($regex, $uri);
    }
}
