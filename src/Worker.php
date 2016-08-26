<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Command\CommandFactoryInterface;
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
     * @var CommandFactoryInterface
     */
    private $commands;

    /**
     * @param DriverInterface $driver
     * @param Event $event
     * @param LoggerInterface $logger
     * @param CommandFactoryInterface $commands
     */
    public function __construct(
        DriverInterface $driver,
        Event $event,
        LoggerInterface $logger,
        CommandFactoryInterface $commands
    ) {
        $this->driver = $driver;
        $this->event = $event;
        $this->logger = $logger;
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
            $options = unserialize($packet);

            if ($this->invoke($options) === false) {
                $this->jobShutdown($options);
                return false;
            }
        } catch (Exception $exception) {
            $this->jobException($options, $exception);
        }

        return true;
    }

    /**
     * Invoke the options command
     *
     * @param AbstractOptions $options
     *
     * @return null|bool
     */
    private function invoke(AbstractOptions $options)
    {
        $this->jobStart($options);

        $result = $this->commands
            ->make($options->command())
            ->withOptions($options)
            ->execute();

        $this->jobFinish($options);

        return $result;
    }

    /**
     * Handles actions related to a job starting
     *
     * @param AbstractOptions $options
     */
    private function jobStart(AbstractOptions $options)
    {
        $this->event->acknowledge($options);
        $this->logger->info(sprintf('`%s` job started', $options->command()));
    }

    /**
     * Handles actions related to a job finishing
     *
     * @param AbstractOptions $options
     */
    private function jobFinish(AbstractOptions $options)
    {
        $this->event->finish($options);
        $this->logger->info(sprintf('`%s` job finished', $options->command()));
    }

    /**
     * Handles actions related to a job shutting down the consumer
     *
     * @param AbstractOptions $options
     */
    private function jobShutdown(AbstractOptions $options)
    {
        $this->logger->notice(sprintf('shutting down by request of `%s`', $options->command()));
    }

    /**
     * Handles actions related to job exceptions
     *
     * @param AbstractOptions $options
     * @param Exception $exception
     */
    private function jobException(AbstractOptions $options, Exception $exception)
    {
        $this->logger->error($exception->getMessage());
        $this->event->reject($options, $exception);
    }
}
