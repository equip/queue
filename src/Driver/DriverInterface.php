<?php

namespace Equip\Queue\Driver;

interface DriverInterface
{
    /**
     * Add a message to the queue
     *
     * @param string $queue
     * @param string $message
     *
     * @return bool
     */
    public function push($queue, $message);

    /**
     * Retrieve a message from the queue
     *
     * @param string $queue
     *
     * @return string
     */
    public function pop($queue);
}
