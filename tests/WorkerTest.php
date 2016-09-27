<?php

namespace Equip\Queue;

use Eloquent\Liberator\Liberator;
use Eloquent\Phony\Phpunit\Phony;
use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Fake\Command;
use League\Tactician\CommandBus;

class WorkerTest extends TestCase
{
    private $driver;

    private $queue;

    private $event;

    private $command_bus;

    private $command;

    protected function setUp()
    {
        $this->driver = Phony::mock(DriverInterface::class);
        $this->queue = Phony::mock(Queue::class);
        $this->event = Phony::mock(Event::class);
        $this->command_bus = Phony::mock(CommandBus::class);
        $this->command = new Command;
    }

    public function testTickNoMessage()
    {
        // Mock
        $this->driver->dequeue->returns(null);

        // Execute
        $result = $this->worker()->tick('test-queue');

        // Verify
        $this->driver->dequeue->calledWith('test-queue');
        $this->assertTrue($result);
    }

    public function testTick()
    {
        // Mock
        $this->driver->dequeue->returns([$this->command, null]);

        // Execute
        $result = $this->worker()->tick('test-queue');

        // Verify
        $this->driver->dequeue->calledWith('test-queue');
        $this->event->acknowledge->calledWith($this->command);
        $this->command_bus->handle->calledWith($this->command);
        $this->driver->processed->calledWith(null);
        $this->event->finish->calledWith($this->command);

        $this->assertTrue($result);
    }

    public function testException()
    {
        // Mock
        $exception = new \Exception;
        $this->driver->dequeue->returns([$this->command, null]);
        $this->command_bus->handle->throws($exception);

        // Execute
        $result = $this->worker()->tick('test-queue');

        // Verify
        $this->driver->dequeue->calledWith('test-queue');
        $this->event->acknowledge->calledWith($this->command);
        $this->command_bus->handle->calledWith($this->command);
        $this->event->reject->calledWith($this->command, $exception);

        $this->assertTrue($result);
    }

    public function testShutdown()
    {
        // Mock
        $worker = $this->worker();
        $worker->shutdown = true;

        // Execute
        $result = $worker->tick('test');

        // Verify
        $this->event->shutdown->called();

        $this->assertFalse($result);
    }

    public function testDrain()
    {
        // Mock
        $worker = $this->worker();
        $worker->drain = true;
        $this->driver->dequeue->returns(null);

        // Execute
        $result = $worker->tick('test');

        // Verify
        $this->event->drained->called();

        $this->assertFalse($result);
    }

    public function testShutdownSwitch()
    {
        // Mock
        $worker = $this->worker();

        // Execute
        $worker->shutdown();

        // Verify
        $this->assertTrue($worker->shutdown);
    }

    public function testDrainSwitch()
    {
        // Mock
        $worker = $this->worker();

        // Execute
        $worker->drain();

        // Verify
        $this->assertTrue($worker->drain);
    }

    private function worker()
    {
        $worker = Phony::partialMock(Worker::class, [
            $this->driver->get(),
            $this->queue->get(),
            $this->event->get(),
            $this->command_bus->get()
        ]);

        return Liberator::liberate($worker->get());
    }
}
