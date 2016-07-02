<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;

class Queue
{
    const DEFAULT_QUEUE = 'queue';
    
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
     * @param string $name
     * @param array $data
     * @param string $queue
     *
     * @return bool
     */
    public function add($name, array $data = [], $queue = self::DEFAULT_QUEUE)
    {
        return $this->driver->push(json_encode([
            'name' => $name,
            'data' => $data,
        ]), $queue);
    }
}
