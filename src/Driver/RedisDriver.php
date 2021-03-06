<?php

namespace Equip\Queue\Driver;

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
    public function enqueue($queue, $command)
    {
        return (bool) $this->redis->rPush($queue, serialize($command));
    }

    /**
     * @inheritdoc
     */
    public function dequeue($queue)
    {
        list(, $message) = $this->redis->blPop($queue, static::TIMEOUT) ?: null;

        return [
            unserialize($message),
            null,
        ];
    }

    /**
     * @inheritdoc
     */
    public function processed($job)
    {
        return true;
    }
}
