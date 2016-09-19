<?php

namespace Equip\Queue;

use Equip\Command\OptionsInterface;
use Exception;
use League\Event\EmitterInterface;
use Psr\Log\LoggerInterface;

class Event
{
    const MESSAGE_ACKNOWLEDGE = 'message.acknowledge';
    const MESSAGE_FINISH = 'message.finish';
    const MESSAGE_REJECT = 'message.reject';
    const QUEUE_SHUTDOWN = 'queue.shutdown';
    const QUEUE_DRAINED = 'queue.drained';

    /**
     * @var EmitterInterface
     */
    private $emitter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param EmitterInterface $emitter
     * @param LoggerInterface $logger
     */
    public function __construct(EmitterInterface $emitter, LoggerInterface $logger)
    {
        $this->emitter = $emitter;
        $this->logger = $logger;
    }

    /**
     * Emits message acknowledgement events
     *
     * @param object $command
     */
    public function acknowledge($command)
    {
        $this->emitter->emit(static::MESSAGE_ACKNOWLEDGE, $command);
        $this->logger->info(sprintf('`%s` job started', get_class($command->command())));
    }

    /**
     * Emits message finished events
     *
     * @param object $command
     */
    public function finish($command)
    {
        $this->emitter->emit(static::MESSAGE_FINISH, $command);
        $this->logger->info(sprintf('`%s` job finished', get_class($command->command())));
    }

    /**
     * Emits message rejection events
     *
     * @param object $command
     * @param Exception $exception
     */
    public function reject($command, Exception $exception)
    {
        $this->emitter->emit(static::MESSAGE_REJECT, $command, $exception);
        $this->logger->error($exception->getMessage());
    }

    /**
     * Handles notifications for shutting down
     */
    public function shutdown()
    {
        $this->emitter->emit(static::QUEUE_SHUTDOWN);
        $this->logger->notice('Shutting down');
    }

    /**
     * Handles notifications for shutting down when drained
     */
    public function drained()
    {
        $this->emitter->emit(static::QUEUE_DRAINED);
        $this->logger->notice('Drained - Shutting down');
    }
}
