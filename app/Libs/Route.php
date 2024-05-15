<?php

namespace PHPvian\Libs;

use Exception;

class Route
{
    protected array $routes = [];
    protected array $routePatterns = [];

    /**
     * Add a GET route.
     *
     * @param string $route Route
     * @param string $controller Controller
     * @param string $action Action
     */
    public function get($route, $controller, $action)
    {
        $this->addRoute('GET', $route, $controller, $action);
    }

    /**
     * Add a POST route.
     *
     * @param string $route Route
     * @param string $controller Controller
     * @param string $action Action
     */
    public function post($route, $controller, $action)
    {
        $this->addRoute('POST', $route, $controller, $action);
    }

    /**
     * Add a route with dynamic validation.
     *
     * @param string $method HTTP Method
     * @param string $route Route
     * @param string $controller Controller
     * @param string $action Action
     */
    private function addRoute($method, $route, $controller, $action)
    {
        $this->routes[$method][$route] = ['controller' => $controller, 'action' => $action];

        // Define pattern to validate dynamic route
        $this->routePatterns[$route] = '/^' . str_replace('/', '\/', $route) . '\/?$/';
    }

    /**
     * Dispatches the current route.
     *
     * @throws Exception If no matching route is found
     */
    public function dispatch()
    {
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        $method = $_SERVER['REQUEST_METHOD'];

        if (!isset($this->routes[$method])) {
            throw new Exception("Unsupported HTTP method: $method");
        }

        foreach ($this->routes[$method] as $route => $handler) {
            if ($this->routeMatches($route, $uri, $handler)) {
                return;
            }
        }

        throw new Exception("No route found for URI: $uri");
    }

    /**
     * Checks if the route matches the current URI.
     *
     * @param string $route Route
     * @param string $uri URI
     * @param array $handler Controller and action associated with the route
     * @return bool Returns true if the route matches the current URI, false otherwise
     * @throws Exception If unable to instantiate controller or find action
     */
    private function routeMatches($route, $uri, $handler)
    {
        if (preg_match($this->routePatterns[$route], $uri, $matches)) {
            $controller = $handler['controller'];
            $action = $handler['action'];

            if (!class_exists($controller)) {
                throw new Exception("Controller class not found: $controller");
            }

            $controllerInstance = new $controller();

            if (!method_exists($controllerInstance, $action)) {
                throw new Exception("Action method not found: $action");
            }

            // Pass URL parameters to controller action
            $params = array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
            call_user_func_array([$controllerInstance, $action], $params);

            return true;
        }

        return false;
    }
}
