<?php

namespace Equip\Queue\Driver;

use Equip\Queue\Fake\Command;
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

    public function testQueue()
    {
        $command = new Command;

        $this->redis
            ->expects($this->once())
            ->method('rPush')
            ->with('test-queue', serialize($command))
            ->willReturn(true);

        $this->assertTrue($this->driver->enqueue('test-queue', $command));
    }

    public function testDequeue()
    {
        $command = new Command;
        $this->redis
            ->expects($this->once())
            ->method('blPop')
            ->with('test-queue', 5)
            ->willReturn([null, serialize($command)]);

        $this->assertEquals([
            $command,
            null,
        ], $this->driver->dequeue('test-queue'));
    }

    public function testDequeueEmpty()
    {
        $this->redis
            ->expects($this->once())
            ->method('blPop')
            ->with('test-queue', 5)
            ->willReturn(null);

        $this->assertEquals([
            false,
            null,
        ], $this->driver->dequeue('test-queue'));
    }

    public function testProcessed()
    {
        $this->assertTrue($this->driver->processed(new \stdClass));
    }
}
