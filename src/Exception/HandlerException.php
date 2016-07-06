<?php

namespace Equip\Queue\Exception;

use Exception;

class HandlerException extends Exception
{
    /**
     * @param string $handler
     *
     * @return static
     */
    public static function invalidHandler($handler)
    {
        return new static(
            sprintf('The handler for `%s` is invalid.', $handler)
        );
    }
}
