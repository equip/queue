<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Exception\HandlerException;
use Equip\Queue\Serializer\JsonSerializer;
use Equip\Queue\Serializer\MessageSerializerInterface;
use Exception;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

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
     * @param LoggerInterface $logger
     * @param MessageSerializerInterface $serializer
     * @param array $handlers
     */
    public function __construct(
        DriverInterface $driver,
        Event $event,
        LoggerInterface $logger,
        MessageSerializerInterface $serializer = null,
        array $handlers = []
    ) {
        $this->driver = $driver;
        $this->event = $event;
        $this->logger = $logger;
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

        $handle = $message->handler();
        $handler = $this->getHandler($handle, $this->handlers);
        if (!$handler) {
            $this->logger->warning(sprintf('Missing `%s` handler', $handle));
            return true;
        }

        try {
            $this->event->acknowledge($message);

            $result = call_user_func($handler, $message);

            $this->event->finish($message);
            $this->logger->info(sprintf('`%s` job finished', $handle));

            if ($result === false) {
                $this->logger->notice('shutting down');
                return false;
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
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
