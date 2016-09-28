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
     * @var Queue
     */
    private $queue;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var CommandBus
     */
    private $command_bus;

    /**
     * Will shutdown the worker on the next tick
     *
     * @var bool
     */
    private $shutdown = false;

    /**
     * Will shutdown the worker when the queue is drained
     *
     * @var bool
     */
    private $drain = false;

    public function __construct(
        DriverInterface $driver,
        Queue $queue,
        Event $event,
        CommandBus $command_bus
    ) {
        $this->driver = $driver;
        $this->queue = $queue;
        $this->event = $event;
        $this->command_bus = $command_bus;
    }

    /**
     * Consumes messages off of the queue
     *
     * @codeCoverageIgnore
     *
     * @param string $queue
     */
    public function consume($queue)
    {
        $this->bindSignals();

        while ($this->tick($queue)) {
            pcntl_signal_dispatch();
        }
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
        if ($this->shutdown) {
            $this->event->shutdown();
            return false;
        }

        list($command, $job) = $this->driver->dequeue($queue);
        if (empty($command) && $this->drain) {
            $this->event->drained();
            return false;
        } elseif (empty($command)) {
            return true;
        }

        try {
            $this->event->acknowledge($command);
            $this->command_bus->handle($command);
            $this->driver->processed($job);
            $this->event->finish($command);
        } catch (Exception $exception) {
            $this->queue->add(sprintf('%s-failed', $queue), $command);
            $this->event->reject($command, $exception);
        }

        return true;
    }

    /**
     * Handles binding POSIX signals appropriately
     *
     * @codeCoverageIgnore
     */
    private function bindSignals()
    {
        // Shutdown the listener
        array_map(function ($signal) {
            pcntl_signal($signal, [$this, 'shutdown']);
        }, [
            SIGTERM,
            SIGINT,
            SIGQUIT,
        ]);

        // Drain the queue
        pcntl_signal(SIGHUP, [$this, 'drain']);
    }

    /**
     * Set the worker to shutdown on the next tick
     */
    public function shutdown()
    {
        $this->shutdown = true;
    }

    /**
     * Set the worker to shutdown when the queue is drained
     */
    public function drain()
    {
        $this->drain = true;
    }
}
