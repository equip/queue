<?php

namespace Equip\Queue;

final class QueuedCommand
{
    /**
     * @var object
     */
    private $command;

    /**
     * @param object $command
     */
    public function __construct($command)
    {
        $this->command = $command;
    }

    /**
     * @return object
     */
    public function command()
    {
        return $this->command;
    }
}
