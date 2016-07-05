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
    private $options = [
        
    ];

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
        $message = $this->driver->pop($queue);
        $decoded_message = json_decode($message, true);

        if (empty($decoded_message)) {
            return true;
        }

        $this->execute(
            $decoded_message['name'],
            $decoded_message['data']
        );

        return true;
    }

    /**
     * Executes the messages handler
     *
     * @param string $name
     * @param array $data
     *
     * @return bool
     */
    private function execute($name, array $data = [])
    {
        if (!isset($this->handlers[$name])) {
            return false;
        }

        $handler = $this->handlers[$name];

        try {
            if (is_callable($handler)) {
                $handler($data);
            }

            $this->acknowledge($name, $data);
        } catch (Exception $exception) {
            $this->reject($exception, $name, $data);
        }
    }

    private function acknowledge($name, array $data = [])
    {
        $this->emitter->emit('queue.acknowledge', $name, $data);
    }

    private function reject(Exception $exception, $name, array $data = [])
    {
        $this->emitter->emit('queue.reject', $exception, $name, $data);
    }
}
