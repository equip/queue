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
        $this->logger->info(sprintf('`%s` job started', get_class($command)));
    }

    /**
     * Emits message finished events
     *
     * @param object $command
     */
    public function finish($command)
    {
        $this->emitter->emit(static::MESSAGE_FINISH, $command);
        $this->logger->info(sprintf('`%s` job finished', get_class($command)));
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
}
