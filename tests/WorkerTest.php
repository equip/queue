<?php

namespace Equip\Queue;

use Eloquent\Liberator\Liberator;
use Eloquent\Phony\Phpunit\Phony;
use Equip\Queue\Command\AurynCommandFactory;
use Equip\Queue\Driver\DriverInterface;
use Equip\Queue\Fake\Command;
use Equip\Queue\Fake\Options;
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
     * @var AurynCommandFactory
     */
    private $factory;

    /**
     * @var string
     */
    private $command;

    /**
     * @var Options
     */
    private $options;

    protected function setUp()
    {
        $this->driver = Phony::mock(DriverInterface::class);
        $this->event = Phony::mock(Event::class);
        $this->factory = Phony::mock(AurynCommandFactory::class);
        $this->command = Phony::partialMock(Command::class);
        $this->options = new Options;
    }

    public function testTicketPacketNull()
    {
        // Mock
        $this->driver->dequeue->returns(null);

        // Execute
        $result = $this->worker()->tick('test-queue');

        // Verify
        $this->driver->dequeue->calledWith('test-queue');
        $this->assertTrue($result);
    }

    public function testShutdown()
    {
        // Mock
        $this->driver->dequeue->returns(serialize([
            'command' => get_class($this->command),
            'options' => $this->options,
        ]));

        $this->factory->make->returns($this->command);
        $this->command->execute->returns(false);

        // Execute
        $result = $this->worker()->tick('test-queue');

        // Verify
        Phony::inOrder(
            $this->event->acknowledge->calledWith(get_class($this->command), $this->options),
            $this->command->withOptions->calledWith($this->options),
            $this->event->finish->calledWith(get_class($this->command), $this->options),
            $this->event->shutdown->calledWith(get_class($this->command))
        );

        $this->assertFalse($result);
    }

    public function testException()
    {
        // Mock
        $exception = new Exception;
        $this->driver->dequeue->returns(serialize([
            'command' => get_class($this->command),
            'options' => $this->options,
        ]));

        $this->factory->make->returns($this->command);
        $this->command->execute->throws($exception);

        // Execute
        $result = $this->worker()->tick('test-queue');

        // Verify
        Phony::inOrder(
            $this->event->acknowledge->calledWith(get_class($this->command), $this->options),
            $this->command->withOptions->calledWith($this->options),
            $this->event->reject->calledWith(get_class($this->command), $this->options, $exception)
        );

        $this->assertTrue($result);
    }

    public function testTick()
    {
        // Mock
        $this->driver->dequeue->returns(serialize([
            'command' => get_class($this->command),
            'options' => $this->options,
        ]));

        $this->factory->make->returns($this->command);
        $this->command->execute->returns(true);

        // Execute
        $result = $this->worker()->tick('test-queue');

        // Verify
        Phony::inOrder(
            $this->event->acknowledge->calledWith(get_class($this->command), $this->options),
            $this->command->withOptions->calledWith($this->options),
            $this->event->finish->calledWith(get_class($this->command), $this->options)
        );

        $this->assertTrue($result);
    }

    public function testConsume()
    {
        // Mock
        $this->driver->dequeue->returns(serialize([
            'command' => get_class($this->command),
            'options' => $this->options,
        ]));

        $this->factory->make->returns($this->command);
        $this->command->execute->returns(false);

        // Execute
        $this->worker()->consume('test-queue');

        // Verify
        Phony::inOrder(
            $this->event->acknowledge->calledWith(get_class($this->command), $this->options),
            $this->command->withOptions->calledWith($this->options),
            $this->event->finish->calledWith(get_class($this->command), $this->options)
        );
    }

    public function testInvoke()
    {
        // Mock
        $this->factory->make->returns($this->command);
        $this->command->execute->returns(true);

        // Execute
        $result = $this->worker()->invoke(get_class($this->command), $this->options);

        // Verify
        Phony::inOrder(
            $this->event->acknowledge->calledWith(get_class($this->command), $this->options),
            $this->command->withOptions->calledWith($this->options),
            $this->event->finish->calledWith(get_class($this->command), $this->options)
        );

        $this->assertTrue($result);
    }

    private function worker()
    {
        $worker = Phony::partialMock(Worker::class, [
            $this->driver->get(),
            $this->event->get(),
            $this->factory->get()
        ]);

        return Liberator::liberate($worker->get());
    }
}
