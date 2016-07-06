<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;

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
        $queue = 'test-queue';
        $name = 'test-job';
        $data = ['test' => 'example'];
        $meta = ['count' => 1];

        $this->driver
            ->expects($this->once())
            ->method('push')
            ->with($queue, json_encode(compact('name', 'data', 'meta')))
            ->willReturn(true);

        $this->assertTrue($this->queue->add(
            $queue,
            $name,
            $data,
            $meta
        ));
    }
}
