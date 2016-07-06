<?php

namespace Equip\Queue;

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
     * @var array
     */
    private $handlers;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @param DriverInterface $driver
     * @param Event $event
     * @param array $handlers
     * @param array $options
     */
    public function __construct(
        DriverInterface $driver,
        Event $event,
        array $handlers = [],
        array $options = []
    ) {
        $this->driver = $driver;
        $this->event = $event;
        $this->handlers = $handlers;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Consumes messages off of the queue
     *
     * @param string $queue
     */
    public function consume($queue)
    {
        declare (ticks = 1);

        while ($this->tick($queue)) { /* NOOP */ }
    }

    /**
     * Handles fetching messages from the queue
     *
     * @param string $queue
     *
     * @return bool
     */
    private function tick($queue)
    {
        $packet = $this->driver->pop($queue);

        $message = json_decode($packet, true);
        if (empty($message)) {
            return true;
        }

        $handler = $this->getHandler($message['name'], $this->handlers);
        if (!$handler) {
            return true;
        }

        if (!is_callable($handler)) {
            return true;
        }

        try {
            call_user_func($handler, $message);

            $this->event->acknowledge($message);
        } catch (Exception $exception) {
            $this->event->reject($message, $exception);
        }

        return true;
    }

    /**
     * Get handler for job
     *
     * @param string $name
     * @param array $router
     *
     * @return null|string
     */
    private function getHandler($name, array $router = [])
    {
        if (!isset($router[$name])) {
            return null;
        }

        return $router[$name];
    }
}
