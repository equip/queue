<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;

class Queue
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Add a message to the queue
     *
     * @param AbstractOptions $message
     *
     * @return bool
     */
    public function add(AbstractOptions $message)
    {
        return $this->driver->enqueue($message);
    }
}
