<?php

namespace Equip\Queue;

use Equip\Command\OptionsInterface;
use Equip\Queue\Command\AurynCommandFactory;
use Equip\Queue\Driver\DriverInterface;
use Exception;

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
     * @var AurynCommandFactory
     */
    private $commands;

    /**
     * @param DriverInterface $driver
     * @param Event $event
     * @param AurynCommandFactory $commands
     */
    public function __construct(
        DriverInterface $driver,
        Event $event,
        AurynCommandFactory $commands
    ) {
        $this->driver = $driver;
        $this->event = $event;
        $this->commands = $commands;
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
        $packet = $this->driver->dequeue($queue);
        if (empty($packet)) {
            return true;
        }

        try {
            list($command, $options) = array_values(unserialize($packet));

            if ($this->invoke($command, $options) === false) {
                $this->event->shutdown($command);
                return false;
            }
        } catch (Exception $exception) {
            $this->event->reject($command, $options, $exception);
        }

        return true;
    }

    /**
     * Invoke the command with the options
     *
     * @param string $command
     * @param OptionsInterface $options
     *
     * @return mixed
     */
    private function invoke($command, OptionsInterface $options)
    {
        $this->event->acknowledge($command, $options);

        $result = $this->commands
            ->make($command)
            ->withOptions($options)
            ->execute();

        $this->event->finish($command, $options);

        return $result;
    }
}
