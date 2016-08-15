<?php

namespace Equip\Queue\Handler;

use Equip\Queue\Exception\HandlerException;
use Equip\Queue\Exception\RouterException;

interface HandlerFactoryInterface
{
    /**
     * Retrieve callable for message
     *
     * @param string $handler
     *
     * @return callable
     *
     * @throws RouterException If route is not found.
     * @throws HandlerException If handler is not acceptable.
     */
    public function get($handler);
}
