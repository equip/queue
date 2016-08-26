<?php

namespace Equip\Queue\Driver;

use Equip\Queue\AbstractMessage;
use Redis;

class RedisDriver implements DriverInterface
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @inheritdoc
     */
    public function enqueue(AbstractMessage $message)
    {
        return (bool) $this->redis->rPush(
            $message->queue(),
            serialize($message)
        );
    }

    /**
     * @inheritdoc
     */
    public function dequeue($queue)
    {
        list($_, $message) = $this->redis->blPop($queue, 5) ?: null;

        return $message;
    }
}
