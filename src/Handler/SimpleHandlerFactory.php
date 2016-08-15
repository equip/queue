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
    public function get($name)
    {
        $handler = $this->getHandler($name);
        if (!$handler) {
            throw HandlerException::notFound($name);
        }

        if (is_string($handler) && class_exists($handler)) {
            $handler = new $handler;
        }

        if (is_callable($handler)) {
            return $handler;
        }

        throw HandlerException::invalidHandler($name);
    }

    /**
     * Get the handler
     *
     * @param string $name
     *
     * @return mixed|null
     */
    private function getHandler($name)
    {
        return isset($this->handlers[$name]) ? $this->handlers[$name] : null;
    }
}
