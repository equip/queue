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
     * Add message to queue
     *
     * @param string $queue
     * @param string $name
     * @param array $data
     *
     * @return bool
     */
    public function add($queue, $name, array $data = [])
    {
        return $this->driver->push(json_encode([
            'name' => $name,
            'data' => $data,
        ]), $queue);
    }
}
