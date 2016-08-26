<?php

namespace Equip\Queue\Driver;

use Equip\Queue\AbstractOptions;

interface DriverInterface
{
    /**
     * Add a message to the queue
     *
     * @param AbstractOptions $message
     *
     * @return bool
     */
    public function enqueue(AbstractOptions $message);

    /**
     * Retrieve a message from the queue
     *
     * @param string $queue
     *
     * @return string
     */
    public function dequeue($queue);
}
