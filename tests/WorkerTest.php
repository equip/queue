<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Exception\HandlerException;
use Equip\Queue\Serializer\JsonSerializer;
use Equip\Queue\Serializer\MessageSerializerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class WorkerTest extends TestCase
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var MessageSerializerInterface
     */
    private $serializer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Worker
     */
    private $worker;

    protected function setUp()
    {
        $this->driver = $this->createMock(DriverInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->event = $this->createMock(Event::class);
        $this->serializer = new JsonSerializer;
        $this->router = $this->createMock(RouterInterface::class);

        $this->worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->serializer,
            $this->router
        );
    }

    public function testTickPacketNull()
    {
        $queue = 'test-queue';
        $this->driver
            ->expects($this->once())
            ->method('dequeue')
            ->with($queue)
            ->willReturn(null);

        $method = static::getProtectedMethod($this->worker, 'tick');
        $this->assertTrue($method->invoke($this->worker, $queue));
    }

    public function testTickInvalidHandler()
    {
        $message = [
            'queue' => 'test-queue',
            'handler' => 'foo',
            'data' => ['foo' => 'bar'],
        ];

        $this->driver
            ->expects($this->once())
            ->method('dequeue')
            ->with($message['queue'])
            ->willReturn(json_encode($message));

        $method = static::getProtectedMethod($this->worker, 'tick');
        $this->assertTrue($method->invoke($this->worker, $message['queue']));
    }

    public function testTickHandlerException()
    {
        $message = new Message('queue', 'foo', ['foo' => 'bar']);
        $exception = new Exception;

        $this->driver
            ->expects($this->once())
            ->method('dequeue')
            ->with($message->queue())
            ->willReturn($this->serializer->serialize($message));

        $this->event
            ->expects($this->once())
            ->method('acknowledge')
            ->with($message);

        $this->event
            ->expects($this->never())
            ->method('finish')
            ->with($message);

        $this->event
            ->expects($this->once())
            ->method('reject')
            ->with($message, $exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($exception->getMessage());

        $this->router
            ->expects($this->once())
            ->method('get')
            ->with($message)
            ->will($this->throwException($exception));

        $worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->serializer,
            $this->router
        );

        $method = static::getProtectedMethod($worker, 'tick');
        $this->assertTrue($method->invoke($worker, $message->queue()));
    }

    public function testTickHandlerReturnFalse()
    {
        $message = new Message('queue', 'foo', ['foo' => 'bar']);

        $this->driver
            ->expects($this->once())
            ->method('dequeue')
            ->with($message->queue())
            ->willReturn($this->serializer->serialize($message));

        $this->event
            ->expects($this->once())
            ->method('acknowledge')
            ->with($message);

        $this->logger
            ->expects($this->once())
            ->method('notice')
            ->with('shutting down by request of `foo`');

        $this->router
            ->expects($this->once())
            ->method('get')
            ->with($message)
            ->willReturn(function () {
                return false;
            });

        $worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->serializer,
            $this->router
        );

        $method = static::getProtectedMethod($worker, 'tick');
        $this->assertFalse($method->invoke($worker, $message->queue()));
    }

    public function testTick()
    {
        $message = new Message('queue', 'foo', ['name' => 'foo']);

        $this->driver
            ->expects($this->once())
            ->method('dequeue')
            ->with($message->queue())
            ->willReturn($this->serializer->serialize($message));

        $this->event
            ->expects($this->once())
            ->method('acknowledge')
            ->with($message);

        $this->event
            ->expects($this->once())
            ->method('finish')
            ->with($message);

        $this->logger
            ->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['`foo` job started'],
                ['`foo` job finished']
            );

        $this->router
            ->expects($this->once())
            ->method('get')
            ->with($message)
            ->willReturn(function ($data) use ($message) {
                $this->assertSame($message->handler(), $data->handler());
            });

        $worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->serializer,
            $this->router
        );

        $method = static::getProtectedMethod($worker, 'tick');
        $this->assertTrue($method->invoke($worker, $message->queue()));
    }

    public function testConsume()
    {
        $message = new Message('queue', 'foo', ['name' => 'foo']);

        $this->driver
            ->expects($this->once())
            ->method('dequeue')
            ->with($message->queue())
            ->willReturn($this->serializer->serialize($message));

        $this->event
            ->expects($this->once())
            ->method('acknowledge')
            ->with($message);

        $this->event
            ->expects($this->once())
            ->method('finish')
            ->with($message);

        $this->router
            ->expects($this->once())
            ->method('get')
            ->willReturn(function () {
                return false;
            });

        $worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->serializer,
            $this->router
        );

        $worker->consume($message->queue());
    }
}
