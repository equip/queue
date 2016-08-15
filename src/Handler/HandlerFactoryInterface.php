<?php

namespace Equip\Queue\Handler;

use Equip\Queue\Exception\HandlerException;

interface HandlerFactoryInterface
{
    /**
     * Retrieve callable for message
     *
     * @param string $handler
     *
     * @return callable
     *
     * @throws HandlerException If handler is not acceptable.
     */
    public function get($handler);
}
