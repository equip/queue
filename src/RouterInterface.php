<?php

namespace Equip\Queue;

use Equip\Queue\Exception\HandlerException;
use Equip\Queue\Exception\RouterException;

interface RouterInterface
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
