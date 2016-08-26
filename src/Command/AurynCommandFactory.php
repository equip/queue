<?php

namespace Equip\Queue\Command;

use Auryn\Injector;
use Equip\Command\CommandInterface;
use Equip\Queue\Exception\CommandException;

class AurynCommandFactory implements CommandFactoryInterface
{
    /**
     * @var Injector
     */
    private $injector;

    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

    /**
     * @inheritdoc
     */
    public function make($command)
    {
        $command = $this->injector->make($command);

        if (!is_a($command, CommandInterface::class)) {
            throw CommandException::invalidCommand($command);
        }

        return $command;
    }
}
