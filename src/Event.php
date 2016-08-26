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
     * @param AbstractOptions $options
     */
    public function acknowledge(AbstractOptions $options)
    {
        array_map(function ($name) use ($options) {
            $this->emitter->emit($name, $options);
        }, [
            static::MESSAGE_ACKNOWLEDGE,
            sprintf('%s.%s', static::MESSAGE_ACKNOWLEDGE, $options->command())
        ]);
    }

    /**
     * Emits message finished events
     *
     * @param AbstractOptions $options
     */
    public function finish(AbstractOptions $options)
    {
        array_map(function ($name) use ($options) {
           $this->emitter->emit($name, $options) ;
        }, [
            static::MESSAGE_FINISH,
            sprintf('%s.%s', static::MESSAGE_FINISH, $options->command())
        ]);
    }

    /**
     * Emits message rejection events
     *
     * @param AbstractOptions $options
     * @param Exception $exception
     */
    public function reject(AbstractOptions $options, Exception $exception)
    {
        array_map(function ($name) use ($options, $exception) {
            $this->emitter->emit($name, $options, $exception);
        }, [
            static::MESSAGE_REJECT,
            sprintf('%s.%s', static::MESSAGE_REJECT, $options->command())
        ]);
    }
}
