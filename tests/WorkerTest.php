<?php

namespace Equip\Queue;

use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Exception\HandlerException;
use Exception;

class WorkerTest extends TestCase
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Worker
     */
    private $worker;

    protected function setUp()
    {
        $this->driver = $this->createMock(DriverInterface::class);
        $this->event = $this->createMock(Event::class);
        $this->worker = new Worker($this->driver, $this->event);
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
        $this->setExpectedExceptionRegExp(
            HandlerException::class,
            '/The handler for `test` is invalid./'
        );

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
            ->method('pop')
            ->with($queue)
            ->willReturn(null);

        $method = static::getProtectedMethod($this->worker, 'tick');
        $this->assertTrue($method->invoke($this->worker, $queue));
    }

    public function testTickInvalidHandler()
    {
        $queue = 'test-queue';
        $message = ['name' => 'foo'];

        $this->driver
            ->expects($this->once())
            ->method('pop')
            ->with($queue)
            ->willReturn(json_encode($message));

        $method = static::getProtectedMethod($this->worker, 'tick');
        $this->assertTrue($method->invoke($this->worker, $queue));
    }

    public function testTickHandlerException()
    {
        $queue = 'test-queue';
        $message = ['name' => 'foo'];
        $exception = new Exception;

        $this->driver
            ->expects($this->once())
            ->method('pop')
            ->with($queue)
            ->willReturn(json_encode($message));

        $this->event
            ->expects($this->once())
            ->method('reject')
            ->with($message, $exception);

        $worker = new Worker(
            $this->driver,
            $this->event,
            ['foo' => function () use ($exception) { throw $exception; }]
        );

        $method = static::getProtectedMethod($worker, 'tick');
        $this->assertTrue($method->invoke($worker, $queue));
    }

    public function testTickHandlerReturnFalse()
    {
        $queue = 'test-queue';
        $message = ['name' => 'foo'];

        $this->driver
            ->expects($this->once())
            ->method('pop')
            ->with($queue)
            ->willReturn(json_encode($message));

        $this->event
            ->expects($this->once())
            ->method('acknowledge')
            ->with($message);

        $worker = new Worker(
            $this->driver,
            $this->event,
            ['foo' => function () { return false; }]
        );

        $method = static::getProtectedMethod($worker, 'tick');
        $this->assertFalse($method->invoke($worker, $queue));
    }

    public function testTick()
    {
        $queue = 'test-queue';
        $message = ['name' => 'foo'];

        $this->driver
            ->expects($this->once())
            ->method('pop')
            ->with($queue)
            ->willReturn(json_encode($message));

        $this->event
            ->expects($this->once())
            ->method('acknowledge')
            ->with($message);

        $worker = new Worker(
            $this->driver,
            $this->event,
            [
                'foo' => function ($data) use ($message) {
                    $this->assertSame($message['name'], $data['name']);
                }
            ]
        );

        $method = static::getProtectedMethod($worker, 'tick');
        $this->assertTrue($method->invoke($worker, $queue));
    }

    public function testConsume()
    {
        $queue = 'test-queue';
        $message = ['name' => 'foo'];

        $this->driver
            ->expects($this->once())
            ->method('pop')
            ->with($queue)
            ->willReturn(json_encode($message));

        $this->event
            ->expects($this->once())
            ->method('acknowledge')
            ->with($message);

        $worker = new Worker(
            $this->driver,
            $this->event,
            ['foo' => function () { return false; }]
        );

        $worker->consume($queue);
    }
}
