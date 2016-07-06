<?php

namespace Equip\Queue\Exception;

use Exception;

class HandlerException extends Exception
{
    /**
     * @param string $name
     *
     * @return static
     */
    public static function invalidHandler($name)
    {
        return new static(
            sprintf('The handler for `%s` is invalid.', $name)
        );
    }
}
