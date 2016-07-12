<?php

namespace Equip\Queue\Driver;

use Equip\Queue\TestCase;
use Redis;

class RedisDriverTest extends TestCase
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * @var RedisDriver
     */
    private $driver;

    protected function setUp()
    {
        $this->redis = $this->createMock(Redis::class);
        $this->driver = new RedisDriver($this->redis);
    }

    public function testPush()
    {
        $queue = 'test-queue';
        $message = json_encode(['test' => 'example']);

        $this->redis
            ->expects($this->once())
            ->method('rPush')
            ->with($queue, $message)
            ->willReturn(true);

        $this->assertTrue($this->driver->enqueue($queue, $message));
    }

    public function testPop()
    {
        $queue = 'test-queue';

        $this->redis
            ->expects($this->once())
            ->method('blPop')
            ->with($queue, 5)
            ->willReturn(['test', 'example']);

        $this->assertSame('example', $this->driver->dequeue($queue));
    }

    public function testPopEmpty()
    {
        $queue = 'test-queue';

        $this->redis
            ->expects($this->once())
            ->method('blPop')
            ->with($queue, 5)
            ->willReturn(null);

        $this->assertNull($this->driver->dequeue($queue));
    }
}
