<?php

namespace Equip\Queue;

use Exception;
use League\Event\EmitterInterface;

class Event
{
    const QUEUE_ACKNOWLEDGE = 'queue.acknowledge';
    const QUEUE_REJECT = 'queue.reject';

    /**
     * @var EmitterInterface
     */
    private $emitter;

    /**
     * @param EmitterInterface $emitter
     */
    public function __construct(EmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * Emits message acknowledgement events
     *
     * @param array $message
     */
    public function acknowledge(array $message)
    {
        array_map(function ($name) use ($message) {
            $this->emitter->emit($name, $message);
        }, [
            static::QUEUE_ACKNOWLEDGE,
            sprintf('%s.%s', static::QUEUE_ACKNOWLEDGE, $message['name'])
        ]);
    }

    /**
     * Emits message rejection events
     *
     * @param array $message
     * @param Exception $exception
     */
    public function reject(array $message, Exception $exception)
    {
        array_map(function ($name) use ($message, $exception) {
            $this->emitter->emit($name, $message, $exception);
        }, [
            static::QUEUE_REJECT,
            sprintf('%s.%s', static::QUEUE_REJECT, $message['name'])
        ]);
    }
}
