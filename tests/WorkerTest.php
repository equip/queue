<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Fake\Message;
use Equip\Queue\Handler\HandlerFactoryInterface;
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
     * @var HandlerFactoryInterface
     */
    private $handlers;

    /**
     * @var Worker
     */
    private $worker;

    protected function setUp()
    {
        $this->driver = $this->createMock(DriverInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->event = $this->createMock(Event::class);
        $this->handlers = $this->createMock(HandlerFactoryInterface::class);

        $this->worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->handlers
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
        $message = new Message;

        $this->driver
            ->expects($this->once())
            ->method('dequeue')
            ->with($message->queue())
            ->willReturn(serialize($message));

        $method = static::getProtectedMethod($this->worker, 'tick');
        $this->assertTrue($method->invoke($this->worker, $message->queue()));
    }

    public function testTickHandlerException()
    {
        $message = new Message;
        $exception = new Exception;

        $this->driver
            ->expects($this->once())
            ->method('dequeue')
            ->with($message->queue())
            ->willReturn(serialize($message));

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

        $this->handlers
            ->expects($this->once())
            ->method('get')
            ->with($message->handler())
            ->will($this->throwException($exception));

        $worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->handlers
        );

        $method = static::getProtectedMethod($worker, 'tick');
        $this->assertTrue($method->invoke($worker, $message->queue()));
    }

    public function testTickHandlerReturnFalse()
    {
        $message = new Message;

        $this->driver
            ->expects($this->once())
            ->method('dequeue')
            ->with($message->queue())
            ->willReturn(serialize($message));

        $this->event
            ->expects($this->once())
            ->method('acknowledge')
            ->with($message);

        $this->logger
            ->expects($this->once())
            ->method('notice')
            ->with('shutting down by request of `example-handler`');

        $this->handlers
            ->expects($this->once())
            ->method('get')
            ->with($message->handler())
            ->willReturn(function () {
                return false;
            });

        $worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->handlers
        );

        $method = static::getProtectedMethod($worker, 'tick');
        $this->assertFalse($method->invoke($worker, $message->queue()));
    }

    public function testTick()
    {
        $message = new Message;

        $this->driver
            ->expects($this->once())
            ->method('dequeue')
            ->with($message->queue())
            ->willReturn(serialize($message));

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
                ['`example-handler` job started'],
                ['`example-handler` job finished']
            );

        $this->handlers
            ->expects($this->once())
            ->method('get')
            ->with($message->handler())
            ->willReturn(function ($data) use ($message) {
                $this->assertSame($message->handler(), $data->handler());
            });

        $worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->handlers
        );

        $method = static::getProtectedMethod($worker, 'tick');
        $this->assertTrue($method->invoke($worker, $message->queue()));
    }

    public function testConsume()
    {
        $message = new Message;

        $this->driver
            ->expects($this->once())
            ->method('dequeue')
            ->with($message->queue())
            ->willReturn(serialize($message));

        $this->event
            ->expects($this->once())
            ->method('acknowledge')
            ->with($message);

        $this->event
            ->expects($this->once())
            ->method('finish')
            ->with($message);

        $this->handlers
            ->expects($this->once())
            ->method('get')
            ->willReturn(function () {
                return false;
            });

        $worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->handlers
        );

        $worker->consume($message->queue());
    }
}
