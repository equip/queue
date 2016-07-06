<?php

namespace Equip\Queue;

class Message
{
    /**
     * @var string
     */
    private $queue;

    /**
     * @var string
     */
    private $handler;

    /**
     * @var array
     */
    private $data;

    /**
     * @param string $queue
     * @param string $handler
     * @param array $data
     */
    public function __construct($queue, $handler, array $data = [])
    {
        $this->queue = $queue;
        $this->handler = $handler;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function queue()
    {
        return $this->queue;
    }

    /**
     * @return string
     */
    public function handler()
    {
        return $this->handler;
    }

    /**
     * @return array
     */
    public function data()
    {
        return $this->data;
    }
}
