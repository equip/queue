<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Exception;
use League\Event\EmitterInterface;

class Worker
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var EmitterInterface
     */
    private $emitter;

    /**
     * @var array
     */
    private $handlers;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @param DriverInterface $driver
     * @param EmitterInterface $emitter
     * @param array $handlers
     * @param array $options
     */
    public function __construct(
        DriverInterface $driver,
        EmitterInterface $emitter,
        array $handlers = [],
        array $options = []
    ) {
        $this->driver = $driver;
        $this->emitter = $emitter;
        $this->handlers = $handlers;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Consumes messages off of the queue
     *
     * @param string $queue
     */
    public function consume($queue)
    {
        declare (ticks = 1);

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
        $packet = $this->driver->pop($queue);

        $message = json_decode($packet, true);
        if (empty($message)) {
            return true;
        }

        $handler = $this->getHandler($message['name'], $this->handlers);
        if (!$handler) {
            return true;
        }

        if (!is_callable($handler)) {
            return true;
        }

        try {
            call_user_func($handler, $message);

            $this->acknowledge($message);
        } catch (Exception $exception) {
            $this->reject($message, $exception);
        }

        return true;
    }

    /**
     * Get handler for job
     *
     * @param string $name
     * @param array $router
     *
     * @return null|string
     */
    private function getHandler($name, array $router = [])
    {
        if (!isset($router[$name])) {
            return null;
        }

        return $router[$name];
    }

    /**
     * Emits message acknowledgement events
     *
     * @param array $message
     */
    private function acknowledge(array $message)
    {
        $event = 'queue.acknowledge';
        array_map(function ($name) use ($message) {
            $this->emitter->emit($name, $message);
        }, [
            $event,
            sprintf('%s.%s', $event, $message['name']),
        ]);
    }

    /**
     * Emits message rejection events
     *
     * @param array $message
     * @param Exception $exception
     */
    private function reject(array $message, Exception $exception)
    {
        $event = 'queue.reject';
        array_map(function ($name) use ($message, $exception) {
            $this->emitter->emit($name, $message, $exception);
        }, [
            $event,
            sprintf('%s.%s', $event, $message['name']),
        ]);
    }
}
