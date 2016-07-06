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
     * @param DriverInterface $driver
     * @param Event $event
     * @param array $handlers
     */
    public function __construct(
        DriverInterface $driver,
        Event $event,
        array $handlers = []
    ) {
        $this->driver = $driver;
        $this->event = $event;
        $this->handlers = $handlers;
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

        try {
            $result = call_user_func($handler, $message);

            $this->event->acknowledge($message);

            if ($result === false) {
                return false;
            }
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
     * @return null|callable
     */
    private function getHandler($name, array $router = [])
    {
        if (!isset($router[$name])) {
            return null;
        }

        $handler = $router[$name];
        if (!is_callable($handler)) {
            return null;
        }

        return $handler;
    }
}
