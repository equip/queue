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
     * @param AbstractMessage $message
     *
     * @return bool
     */
    public function add(AbstractMessage $message)
    {
        return $this->driver->enqueue($message);
    }
}
