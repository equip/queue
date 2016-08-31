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

    private $event;

    private $command_bus;

    private $command;

    protected function setUp()
    {
        $this->driver = Phony::mock(DriverInterface::class);
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
        $this->driver->dequeue->returns(serialize($this->command));

        // Execute
        $result = $this->worker()->tick('test-queue');

        // Verify
        $this->driver->dequeue->calledWith('test-queue');
        $this->event->acknowledge->calledWith($this->command);
        $this->command_bus->handle->calledWith($this->command);
        $this->event->finish->calledWith($this->command);

        $this->assertTrue($result);
    }

    public function testException()
    {
        // Mock
        $exception = new \Exception;
        $this->driver->dequeue->returns(serialize($this->command));
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

    private function worker()
    {
        $worker = Phony::partialMock(Worker::class, [
            $this->driver->get(),
            $this->event->get(),
            $this->command_bus->get()
        ]);

        return Liberator::liberate($worker->get());
    }
}
