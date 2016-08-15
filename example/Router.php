<?php

use Equip\Queue\Exception\HandlerException;
use Equip\Queue\Exception\RouterException;
use Equip\Queue\RouterInterface;

class Router implements RouterInterface
{
    /**
     * @var array
     */
    private $routes;

    /**
     * @param array $routes
     */
    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
    }

    /**
     * @inheritdoc
     */
    public function get($handler)
    {
        $route = $this->getRoute($handler);
        if (!$route) {
            throw RouterException::routeNotFound($handler);
        }

        if (is_callable($route)) {
            return $route;
        }

        if (class_exists($route)) {
            return new $route;
        }

        throw HandlerException::invalidHandler($handler);
    }

    private function getRoute($handler)
    {
        return isset($this->routes[$handler]) ? $this->routes[$handler] : null;
    }
}
