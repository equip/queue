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
     * @var Worker
     */
    private $worker;

    protected function setUp()
    {
        $this->driver = $this->createMock(DriverInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->event = $this->createMock(Event::class);
        $this->serializer = new JsonSerializer;
        $this->worker = new Worker($this->driver, $this->event, $this->logger);
    }

    public function testGetHandler()
    {
        $name = 'test';
        $routes = [
            $name => function () {},
        ];

        $method = static::getProtectedMethod($this->worker, 'getHandler');
        $result = $method->invoke($this->worker, $name, $routes);

        $this->assertSame($routes[$name], $result);
    }

    public function testGetHandlerNotCallable()
    {
        $this->expectException(HandlerException::class);
        $this->expectExceptionMessage('The handler for `test` is invalid.');

        $name = 'test';
        $routes = [
            $name => 'test',
        ];

        $method = static::getProtectedMethod($this->worker, 'getHandler');
        $method->invoke($this->worker, $name, $routes);
    }

    public function testGetHandlerNoHandler()
    {
        $method = static::getProtectedMethod($this->worker, 'getHandler');
        $this->assertNull($method->invoke($this->worker, 'test', []));
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

        $worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->serializer,
            ['foo' => function () use ($exception) { throw $exception; }]
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

        $worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->serializer,
            ['foo' => function () { return false; }]
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

        $worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->serializer,
            [
                'foo' => function ($data) use ($message) {
                    $this->assertSame($message->handler(), $data->handler());
                }
            ]
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

        $worker = new Worker(
            $this->driver,
            $this->event,
            $this->logger,
            $this->serializer,
            ['foo' => function () { return false; }]
        );

        $worker->consume($message->queue());
    }
}
