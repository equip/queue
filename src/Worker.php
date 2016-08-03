<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Exception\HandlerException;
use Equip\Queue\Serializer\JsonSerializer;
use Equip\Queue\Serializer\MessageSerializerInterface;
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
     * @var MessageSerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $handlers;

    /**
     * @param DriverInterface $driver
     * @param Event $event
     * @param MessageSerializerInterface $serializer
     * @param array $handlers
     */
    public function __construct(
        DriverInterface $driver,
        Event $event,
        MessageSerializerInterface $serializer = null,
        array $handlers = []
    ) {
        $this->driver = $driver;
        $this->event = $event;
        $this->serializer = $serializer ?: new JsonSerializer;
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
        $packet = $this->driver->dequeue($queue);
        if (empty($packet)) {
             return true;
        }

        $message = $this->serializer->deserialize($packet);

        $handler = $this->getHandler($message->handler(), $this->handlers);
        if (!$handler) {
            return true;
        }

        try {
            $this->event->acknowledge($message);

            $result = call_user_func($handler, $message);

            $this->event->finish($message);

            if ($result === false) {
                return false;
            }
        } catch (Exception $exception) {
            $this->event->reject($message, $exception);
        }

        return true;
    }

    /**
     * @param string $handler
     * @param array $router
     *
     * @return null|callable
     * @throws HandlerException If handler is not callable
     */
    private function getHandler($handler, array $router = [])
    {
        if (!isset($router[$handler])) {
            return null;
        }

        $callable = $router[$handler];
        if (!is_callable($callable)) {
            throw HandlerException::invalidHandler($handler);
        }

        return $callable;
    }
}
