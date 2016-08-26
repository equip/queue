<?php

namespace Equip\Queue\Driver;

use Equip\Queue\Fake\Command;
use Equip\Queue\Fake\Options;
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
        $message = [
            'command' => Command::class,
            'options' => new Options,
        ];

        $this->redis
            ->expects($this->once())
            ->method('rPush')
            ->with('test-queue', serialize($message))
            ->willReturn(true);

        $this->assertTrue($this->driver->enqueue('test-queue', $message));
    }

    public function testPop()
    {
        $this->redis
            ->expects($this->once())
            ->method('blPop')
            ->with('test-queue', 5)
            ->willReturn(['test', 'example']);

        $this->assertSame('example', $this->driver->dequeue('test-queue'));
    }

    public function testPopEmpty()
    {
        $this->redis
            ->expects($this->once())
            ->method('blPop')
            ->with('test-queue', 5)
            ->willReturn(null);

        $this->assertNull($this->driver->dequeue('test-queue'));
    }
}
