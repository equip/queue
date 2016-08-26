<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Handler\HandlerFactoryInterface;
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
     * @var HandlerFactoryInterface
     */
    private $handlers;

    /**
     * @param DriverInterface $driver
     * @param Event $event
     * @param LoggerInterface $logger
     * @param HandlerFactoryInterface $handlers
     */
    public function __construct(
        DriverInterface $driver,
        Event $event,
        LoggerInterface $logger,
        HandlerFactoryInterface $handlers
    ) {
        $this->driver = $driver;
        $this->event = $event;
        $this->logger = $logger;
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

        try {
            $message = unserialize($packet);

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
     * @param AbstractOptions $message
     *
     * @return null|bool
     */
    private function invoke(AbstractOptions $message)
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
     * @param AbstractOptions $message
     */
    private function jobStart(AbstractOptions $message)
    {
        $this->event->acknowledge($message);
        $this->logger->info(sprintf('`%s` job started', $message->handler()));
    }

    /**
     * Handles actions related to a job finishing
     *
     * @param AbstractOptions $message
     */
    private function jobFinish(AbstractOptions $message)
    {
        $this->event->finish($message);
        $this->logger->info(sprintf('`%s` job finished', $message->handler()));
    }

    /**
     * Handles actions related to a job shutting down the consumer
     *
     * @param AbstractOptions $message
     */
    private function jobShutdown(AbstractOptions $message)
    {
        $this->logger->notice(sprintf('shutting down by request of `%s`', $message->handler()));
    }

    /**
     * Handles actions related to job exceptions
     *
     * @param AbstractOptions $message
     * @param Exception $exception
     */
    private function jobException(AbstractOptions $message, Exception $exception)
    {
        $this->logger->error($exception->getMessage());
        $this->event->reject($message, $exception);
    }
}
