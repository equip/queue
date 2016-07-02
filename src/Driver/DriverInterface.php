<?php

namespace Equip\Queue\Driver;

interface DriverInterface
{
    /**
     * Add a message to the queue
     * 
     * @param string $message
     * @param string $queue
     *
     * @return bool
     */
    public function push($message, $queue);

    /**
     * Retrieve a message from the queue
     * 
     * @param string $queue
     *
     * @return string
     */
    public function pop($queue);
}
