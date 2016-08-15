<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Handler\HandlerFactoryInterface;
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
     * @var HandlerFactoryInterface
     */
    private $handlers;

    /**
     * @param DriverInterface $driver
     * @param Event $event
     * @param LoggerInterface $logger
     * @param MessageSerializerInterface $serializer
     * @param HandlerFactoryInterface $handlers
     */
    public function __construct(
        DriverInterface $driver,
        Event $event,
        LoggerInterface $logger,
        MessageSerializerInterface $serializer = null,
        HandlerFactoryInterface $handlers
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
    protected function tick($queue)
    {
        $packet = $this->driver->dequeue($queue);
        if (empty($packet)) {
            return true;
        }

        $message = $this->serializer->deserialize($packet);
        try {
            if ($this->invoke($message) === false) {
                $this->jobShutdown($message);
                return false;
            }
        } catch (Exception $exception) {
            $this->jobException($message, $exception);
        }

        return true;
    }

    /**
     * Invoke the messages handler
     *
     * @param Message $message
     *
     * @return null|bool
     */
    private function invoke(Message $message)
    {
        $this->jobStart($message);

        $result = call_user_func(
            $this->handlers->get($message->handler()),
            $message
        );

        $this->jobFinish($message);

        return $result;
    }

    /**
     * Handles actions related to a job starting
     *
     * @param Message $message
     */
    private function jobStart(Message $message)
    {
        $this->event->acknowledge($message);
        $this->logger->info(sprintf('`%s` job started', $message->handler()));
    }

    /**
     * Handles actions related to a job finishing
     *
     * @param Message $message
     */
    private function jobFinish(Message $message)
    {
        $this->event->finish($message);
        $this->logger->info(sprintf('`%s` job finished', $message->handler()));
    }

    /**
     * Handles actions related to a job shutting down the consumer
     *
     * @param Message $message
     */
    private function jobShutdown(Message $message)
    {
        $this->logger->notice(sprintf('shutting down by request of `%s`', $message->handler()));
    }

    /**
     * Handles actions related to job exceptions
     *
     * @param Message $message
     * @param Exception $exception
     */
    private function jobException(Message $message, Exception $exception)
    {
        $this->logger->error($exception->getMessage());
        $this->event->reject($message, $exception);
    }
}
