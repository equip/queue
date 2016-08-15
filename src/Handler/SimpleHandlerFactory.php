<?php

namespace Equip\Queue\Handler;

use Equip\Queue\Exception\HandlerException;

class SimpleHandlerFactory implements HandlerFactoryInterface
{
    /**
     * @var array
     */
    private $handlers;

    /**
     * @param array $handlers
     */
    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    /**
     * @inheritdoc
     */
    public function get($handler)
    {
        $route = $this->getHandler($handler);
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
     * Get the handler
     *
     * @param string $handler
     *
     * @return mixed|null
     */
    private function getHandler($handler)
    {
        return isset($this->handlers[$handler]) ? $this->handlers[$handler] : null;
    }
}
