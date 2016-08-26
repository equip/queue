<?php

namespace Equip\Queue\Command;

use Equip\Command\CommandInterface;
use Equip\Queue\Exception\CommandException;
use Equip\Queue\Exception\HandlerException;

class SimpleCommandFactory implements CommandFactoryInterface
{
    public function make($command)
    {
        if (!class_exists($command)) {
            throw CommandException::notFound($command);
        }

        $command = new $command;

        if (!($command instanceof CommandInterface)) {
            throw CommandException::invalidCommand($command);
        }

        return $command;
    }
}
