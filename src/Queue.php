<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Serializer\JsonSerializer;
use Equip\Queue\Serializer\MessageSerializerInterface;

class Queue
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var MessageSerializerInterface
     */
    private $serializer;

    /**
     * @param DriverInterface $driver
     * @param MessageSerializerInterface $serializer
     */
    public function __construct(DriverInterface $driver, MessageSerializerInterface $serializer = null)
    {
        $this->driver = $driver;
        $this->serializer = $serializer ?: new JsonSerializer;
    }

    /**
     * Adds a message to the queue
     *
     * @param Message $message
     *
     * @return bool
     */
    public function add(Message $message)
    {
        return $this->driver->enqueue(
            $message->queue(),
            $this->serializer->serialize($message)
        );
    }
}
