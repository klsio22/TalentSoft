<?php

namespace Core\Router;

use Core\Http\Request;
use Core\Exceptions\HTTPException;

class Router
{
    private static $instance;
    private $routes = [];
    private $groups = [];

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addRoute(Route $route): Route
    {
        $this->routes[] = $route;
        return $route;
    }

    public function addGroup(array $attributes, \Closure $callback): void
    {
        $this->groups[] = $attributes;
        $callback();
        array_pop($this->groups);
    }

    public function getRouteSize(): int
    {
        return sizeof($this->routes);
    }

    public function getRoute(int $index): Route
    {
        return $this->routes[$index];
    }

    public function getRoutePathByName(string $name, array $params = []): string
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                $routePath = $route->getUri();
                if (!empty($params)) {
                    $routePath .= '?' . http_build_query($params);
                }
                return $routePath;
            }
        }
        throw new HTTPException('Route with name ' . $name . ' not found.', 404);
    }

    public function dispatch()
    {
        $request = new Request();
        foreach ($this->routes as $route) {
            if ($route->match($request)) {
                $route->runMiddlewares($request);
                $class = $route->getControllerName();
                $action = $route->getActionName();
                $controller = new $class();
                $controller->$action($request);
                return $controller;
            }
        }
        $this->redirectTo404();
    }

    private function redirectTo404(): void
    {
        header('Location: ' . $this->getRoutePathByName('errors.404'));
        exit;
    }

    public static function init(): void
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            require Constants::rootPath()->join('config/routes.php');
            Router::getInstance()->dispatch();
        }
    }
}
