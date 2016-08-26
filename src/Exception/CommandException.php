<?php

namespace Equip\Queue\Exception;

use Exception;

class CommandException extends Exception
{
    const INVALID = 1000;

    /**
     * @param string $name
     *
     * @return static
     */
    public static function invalidCommand($name)
    {
        return new static(
            sprintf('The command for `%s` is invalid.', $name),
            static::INVALID
        );
    }
}
