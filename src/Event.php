<?php

namespace Equip\Queue;

use Exception;
use League\Event\EmitterInterface;

class Event
{
    const MESSAGE_ACKNOWLEDGE = 'message.acknowledge';
    const MESSAGE_FINISH = 'message.finish';
    const MESSAGE_REJECT = 'message.reject';

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
     * @param AbstractMessage $message
     */
    public function acknowledge(AbstractMessage $message)
    {
        array_map(function ($name) use ($message) {
            $this->emitter->emit($name, $message);
        }, [
            static::MESSAGE_ACKNOWLEDGE,
            sprintf('%s.%s', static::MESSAGE_ACKNOWLEDGE, $message->handler())
        ]);
    }

    /**
     * Emits message finished events
     *
     * @param AbstractMessage $message
     */
    public function finish(AbstractMessage $message)
    {
        array_map(function ($name) use ($message) {
           $this->emitter->emit($name, $message) ;
        }, [
            static::MESSAGE_FINISH,
            sprintf('%s.%s', static::MESSAGE_FINISH, $message->handler())
        ]);
    }

    /**
     * Emits message rejection events
     *
     * @param AbstractMessage $message
     * @param Exception $exception
     */
    public function reject(AbstractMessage $message, Exception $exception)
    {
        array_map(function ($name) use ($message, $exception) {
            $this->emitter->emit($name, $message, $exception);
        }, [
            static::MESSAGE_REJECT,
            sprintf('%s.%s', static::MESSAGE_REJECT, $message->handler())
        ]);
    }
}
