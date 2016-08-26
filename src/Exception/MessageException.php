<?php

namespace Equip\Queue\Exception;

use Exception;

class MessageException extends Exception
{
    /**
     * @param string $name
     *
     * @return static
     */
    public static function missingProperty($name)
    {
        return new static(
            sprintf('Missing property `%s`.', $name)
        );
    }
}
