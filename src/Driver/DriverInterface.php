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
    public function enqueue($queue, $message);

    /**
     * Retrieve a message from the queue
     *
     * @param string $queue
     *
     * @return string
     */
    public function dequeue($queue);
}
