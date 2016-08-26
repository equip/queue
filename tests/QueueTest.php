<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Fake\Command;
use Equip\Queue\Fake\Options;

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
        $command = Command::class;
        $options = new Options;
        $queue = 'test-queue';

        $this->driver
            ->expects($this->once())
            ->method('enqueue')
            ->with($queue, compact('command', 'options'))
            ->willReturn(true);

        $this->assertTrue($this->queue->add($queue, $command, $options));
    }
}
