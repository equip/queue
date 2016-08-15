<?php

namespace Equip\Queue\Router;

use Equip\Queue\Exception\HandlerException;
use Equip\Queue\Exception\RouterException;

interface RouteFactoryInterface
{
    /**
     * Retrieve callable for message
     *
     * @param string $handler
     *
     * @return callable
     *
     * @throws RouterException If router is not found.
     * @throws HandlerException If handler is not acceptable.
     */
    public function get($handler);
}
