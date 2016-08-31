<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Fake\Command;

class QueueTest extends TestCase
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var Queue
     */
    private $queue;

    protected function setUp()
    {
        $this->driver = $this->createMock(DriverInterface::class);
        $this->queue = new Queue($this->driver);
    }

    public function testAdd()
    {
        $command = new Command;
        $queue = 'test-queue';

        $this->driver
            ->expects($this->once())
            ->method('enqueue')
            ->with($queue, $command)
            ->willReturn(true);

        $this->assertTrue($this->queue->add($queue, $command));
    }
}
