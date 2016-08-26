<?php

namespace Equip\Queue;

use Equip\Command\OptionsInterface;
use Equip\Command\OptionsSerializerTrait;
use Equip\Queue\Exception\MessageException;

abstract class AbstractOptions implements OptionsInterface
{
    use OptionsSerializerTrait;

    /**
     * @var string
     */
    protected $command;

    /**
     * @var string
     */
    protected $handler;

    /**
     * @var string
     */
    protected $queue;

    /**
     * Returns the command class name
     *
     * @return string
     *
     * @throws MessageException If the command property isn't set
     */
    public function command()
    {
        if (!$this->command) {
            throw MessageException::missingProperty('command');
        }

        return $this->command;
    }

    /**
     * Returns the handler name
     *
     * @return string
     *
     * @throws MessageException If the handler property isn't set
     */
    public function handler()
    {
        if (!$this->handler) {
            throw MessageException::missingProperty('handler');
        }

        return $this->handler;
    }

    /**
     * Returns the queue name
     *
     * @return string
     *
     * @throws MessageException If the queue property isn't set
     */
    public function queue()
    {
        if (!$this->queue) {
            throw MessageException::missingProperty('queue');
        }

        return $this->queue;
    }
}
