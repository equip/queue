<?php

namespace Equip\Queue\Driver;

interface DriverInterface
{
    /**
     * Add a command to the queue
     *
     * @param string $queue
     * @param object $command
     *
     * @return bool
     */
    public function enqueue($queue, $command);

    /**
     * Retrieve a message from the queue
     *
     * @param string $queue
     *
     * @return string
     */
    public function dequeue($queue);
}
