<?php

namespace Equip\Queue;

use Equip\Command\OptionsInterface;
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
     * @param string $queue
     * @param string $command
     *
     * @return bool
     */
    public function add($queue, $command)
    {
        return $this->driver->enqueue($queue, $command);
    }
}
