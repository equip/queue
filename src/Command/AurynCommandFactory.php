<?php

namespace Equip\Queue\Command;

use Auryn\Injector;
use Equip\Command\CommandInterface;
use Equip\Queue\Exception\CommandException;

class AurynCommandFactory
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
        if (!class_exists($command)) {
            throw CommandException::notFound($command);
        }

        $command = $this->injector->make($command);

        if (!($command instanceof CommandInterface)) {
            throw CommandException::invalidCommand($command);
        }

        return $command;
    }
}
