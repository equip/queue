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
     * @param $name
     * @param array $data
     * @param array $meta
     *
     * @return bool
     */
    public function add($queue, $name, $data = [], $meta = [])
    {
        return $this->driver->push(
            $queue,
            json_encode(compact('name', 'data', 'meta'))
        );
    }
}
