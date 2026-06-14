<?php

class Router
{
    private array $routes = [];

    public function get($uri, $action)
    {
        $this->routes['GET'][$uri] = $action;
    }

    public function post($uri, $action)
    {
        $this->routes['POST'][$uri] = $action;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        $uri = parse_url(
            $_SERVER['REQUEST_URI'],
            PHP_URL_PATH
        );

        $basePath = '/mobile-shop-ltw/public';

        if (str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        if ($uri === '') {
            $uri = '/';
        }

        if (!isset($this->routes[$method])) {
            http_response_code(404);

            echo json_encode([
                'success' => false,
                'message' => 'Method not supported'
            ]);

            return;
        }

        foreach ($this->routes[$method] as $route => $handler) {

            $pattern = preg_replace(
                '/\{[a-zA-Z_]+\}/',
                '([^\/]+)',
                $route
            );

            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {

                array_shift($matches);

                [$controller, $action] = $handler;

                $instance = new $controller();

                call_user_func_array(
                    [$instance, $action],
                    $matches
                );

                return;
            }
        }

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Route not found'
        ]);
    }
}