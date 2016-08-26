<?php

namespace Equip\Queue\Driver;

use Equip\Queue\AbstractOptions;

interface DriverInterface
{
    /**
     * Add a message to the queue
     *
     * @param string $queue
     * @param array $message
     *
     * @return bool
     */
    public function enqueue($queue, array $message);

    /**
     * Retrieve a message from the queue
     *
     * @param string $queue
     *
     * @return string
     */
    public function dequeue($queue);
}
