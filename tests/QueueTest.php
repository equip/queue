<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Serializer\MessageSerializerInterface;

class QueueTest extends TestCase
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var MessageSerializerInterface
     */
    private $serializer;

    /**
     * @var Queue
     */
    private $queue;

    protected function setUp()
    {
        $this->driver = $this->createMock(DriverInterface::class);
        $this->serializer = $this->createMock(MessageSerializerInterface::class);
        $this->queue = new Queue($this->driver, $this->serializer);
    }

    public function testAdd()
    {
        $queue = 'queue';
        $handler = 'handler';
        $data = ['foo' => 'bar'];

        $serialized_message = json_encode(compact('queue', 'handler', 'data'));
        $message = new Message($queue, $handler, $data);

        $this->driver
            ->expects($this->once())
            ->method('push')
            ->with('queue', $serialized_message)
            ->willReturn(true);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($message)
            ->willReturn($serialized_message);

        $this->assertTrue($this->queue->add($message));
    }
}
