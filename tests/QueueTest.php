<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Fake\Options;
use Equip\Queue\Serializer\MessageSerializerInterface;

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
        $message = new Options;

        $this->driver
            ->expects($this->once())
            ->method('enqueue')
            ->with($message)
            ->willReturn(true);

        $this->assertTrue($this->queue->add($message));
    }
}
