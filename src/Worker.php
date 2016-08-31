<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Exception;
use League\Tactician\CommandBus;

class Worker
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var CommandBus
     */
    private $command_bus;

    public function __construct(
        DriverInterface $driver,
        Event $event,
        CommandBus $command_bus
    ) {
        $this->driver = $driver;
        $this->event = $event;
        $this->command_bus = $command_bus;
    }

    /**
     * Consumes messages off of the queue
     *
     * @param string $queue
     */
    public function consume($queue)
    {
        while ($this->tick($queue)) { /* NOOP */ }
    }

    /**
     * Handles fetching messages from the queue
     *
     * @param string $queue
     *
     * @return bool
     */
    protected function tick($queue)
    {
        $message = $this->driver->dequeue($queue);
        if (empty($message)) {
            return true;
        }

        $command = unserialize($message);
        try {
            $this->event->acknowledge($command);
            $this->command_bus->handle($command);
            $this->event->finish($command);
        } catch (Exception $exception) {
            $this->event->reject($command, $exception);
        }

        return true;
    }
}
