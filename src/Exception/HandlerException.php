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

    /**
     * @param string $handler
     *
     * @return static
     */
    public static function notFound($handler)
    {
        return new static(
            sprintf('`%s` handler not found.', $handler)
        );
    }
}
