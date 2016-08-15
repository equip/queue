<?php

namespace Equip\Queue\Handler;

use Equip\Queue\Exception\HandlerException;
use Equip\Queue\Exception\RouterException;

class SimpleHandlerFactory implements HandlerFactoryInterface
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
            throw HandlerException::notFound($handler);
        }

        if (is_string($route) && class_exists($route)) {
            $route = new $route;
        }

        if (is_callable($route)) {
            return $route;
        }

        throw HandlerException::invalidHandler($handler);
    }

    /**
     * Get the routes handler
     *
     * @param string $handler
     *
     * @return mixed|null
     */
    private function getRoute($handler)
    {
        return isset($this->routes[$handler]) ? $this->routes[$handler] : null;
    }
}
