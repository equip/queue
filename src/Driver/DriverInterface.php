<?php

namespace Equip\Queue\Driver;

use Equip\Queue\AbstractMessage;

interface DriverInterface
{
    /**
     * Add a message to the queue
     *
     * @param AbstractMessage $message
     *
     * @return bool
     */
    public function enqueue(AbstractMessage $message);

    /**
     * Retrieve a message from the queue
     *
     * @param string $queue
     *
     * @return string
     */
    public function dequeue($queue);
}
