<?php

namespace Equip\Queue\Exception;

use Exception;

class RouterException extends Exception
{
    /**
     * @param string $handler
     *
     * @return static
     */
    public static function routeNotFound($handler)
    {
        return new static(
            sprintf('Route not found for `%s`.', $handler)
        );
    }
}
