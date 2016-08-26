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
     * @param string $command
     * @param OptionsInterface $options
     */
    public function acknowledge($command, OptionsInterface $options)
    {
        array_map(function ($name) use ($options) {
            $this->emitter->emit($name, $options);
        }, [
            static::MESSAGE_ACKNOWLEDGE,
            sprintf('%s.%s', static::MESSAGE_ACKNOWLEDGE, $command)
        ]);

        $this->logger->info(sprintf('`%s` job started', $command));
    }

    /**
     * Emits message finished events
     *
     * @param string $command
     * @param OptionsInterface $options
     */
    public function finish($command, OptionsInterface $options)
    {
        array_map(function ($name) use ($options) {
           $this->emitter->emit($name, $options) ;
        }, [
            static::MESSAGE_FINISH,
            sprintf('%s.%s', static::MESSAGE_FINISH, $command)
        ]);

        $this->logger->info(sprintf('`%s` job finished', $command));
    }

    /**
     * Emits message rejection events
     *
     * @param string $command
     * @param OptionsInterface $options
     * @param Exception $exception
     */
    public function reject($command, OptionsInterface $options, Exception $exception)
    {
        array_map(function ($name) use ($options, $exception) {
            $this->emitter->emit($name, $options, $exception);
        }, [
            static::MESSAGE_REJECT,
            sprintf('%s.%s', static::MESSAGE_REJECT, $command)
        ]);

        $this->logger->error((string) $exception);
    }

    /**
     * Emits message shutdown events
     *
     * @param string $command
     */
    public function shutdown($command)
    {
        array_map(function ($name) {
            $this->emitter->emit($name) ;
        }, [
            static::QUEUE_SHUTDOWN,
            sprintf('%s.%s', static::QUEUE_SHUTDOWN, $command)
        ]);

        $this->logger->notice(sprintf('shutting down by request of `%s`', $command));
    }
}
