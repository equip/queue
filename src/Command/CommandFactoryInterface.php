<?php

namespace Equip\Queue\Command;

use Equip\Command\CommandInterface;
use Equip\Queue\Exception\CommandException;
use Equip\Queue\Exception\HandlerException;

interface CommandFactoryInterface
{
    /**
     * Instantiates a command object
     *
     * @param string $command
     *
     * @return CommandInterface
     *
     * @throws CommandException If command class doesn't exist
     * @throws CommandException If command is not an instance of CommandInterface
     */
    public function make($command);
}
