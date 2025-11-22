<?php
// FILE: /app/core/Router.php

/**
 * Router Class
 * Handles URL routing and dispatching to controllers
 * SplashHR - Multi-tenant HR & Payroll SaaS Platform
 */

class Router {

    private $routes = [];

    /**
     * Add a GET route
     * @param string $path
     * @param string $controller
     * @param string $method
     */
    public function get($path, $controller, $method) {
        $this->addRoute('GET', $path, $controller, $method);
    }

    /**
     * Add a POST route
     * @param string $path
     * @param string $controller
     * @param string $method
     */
    public function post($path, $controller, $method) {
        $this->addRoute('POST', $path, $controller, $method);
    }

    /**
     * Add a route
     * @param string $httpMethod
     * @param string $path
     * @param string $controller
     * @param string $method
     */
    private function addRoute($httpMethod, $path, $controller, $method) {
        $this->routes[] = [
            'method' => $httpMethod,
            'path' => $path,
            'controller' => $controller,
            'action' => $method
        ];
    }

    /**
     * Dispatch the request
     */
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];

        // Remove query string
        $requestUri = strtok($requestUri, '?');

        // Remove trailing slash
        $requestUri = rtrim($requestUri, '/');

        // Default to home if empty
        if (empty($requestUri)) {
            $requestUri = '/';
        }

        foreach ($this->routes as $route) {
            $pattern = $this->convertPathToRegex($route['path']);

            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                // Remove the full match
                array_shift($matches);

                // Load and instantiate controller
                $controllerFile = __DIR__ . '/../controllers/' . $route['controller'] . '.php';

                if (!file_exists($controllerFile)) {
                    $this->notFound();
                    return;
                }

                require_once $controllerFile;

                $controller = new $route['controller']();
                $action = $route['action'];

                if (!method_exists($controller, $action)) {
                    $this->notFound();
                    return;
                }

                // Call the controller method with parameters
                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }

        // No route matched
        $this->notFound();
    }

    /**
     * Convert path pattern to regex
     * @param string $path
     * @return string
     */
    private function convertPathToRegex($path) {
        // Convert :param to regex capture group
        $pattern = preg_replace('/\/:([^\/]+)/', '/([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Handle 404 Not Found
     */
    private function notFound() {
        http_response_code(404);
        echo "404 - Page Not Found";
    }
}
