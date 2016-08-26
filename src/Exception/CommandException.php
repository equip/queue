<?php

namespace Equip\Queue\Exception;

use Exception;

class CommandException extends Exception
{
    /**
     * @param string $name
     *
     * @return static
     */
    public static function invalidCommand($name)
    {
        return new static(
            sprintf('The command for `%s` is invalid.', $name)
        );
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public static function notFound($name)
    {
        return new static(
            sprintf('`%s` command not found.', $name)
        );
    }
}
