<?php

namespace Equip\Queue;

use Equip\Queue\Fake\Command;
use Exception;
use League\Event\EmitterInterface;
use Psr\Log\LoggerInterface;

class EventTest extends TestCase
{
    /**
     * @var EmitterInterface
     */
    private $emitter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Command
     */
    private $command;

    protected function setUp()
    {
        $this->emitter = $this->createMock(EmitterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->event = new Event($this->emitter, $this->logger);
        $this->command = new QueuedCommand(new Command);
    }

    public function testAcknowledge()
    {
        $this->emitter
            ->expects($this->once())
            ->method('emit')
            ->with(Event::MESSAGE_ACKNOWLEDGE, $this->command);

        $this->event->acknowledge($this->command);
    }

    public function testFinish()
    {
        $this->emitter
            ->expects($this->once())
            ->method('emit')
            ->with(Event::MESSAGE_FINISH, $this->command);

        $this->event->finish($this->command);
    }

    public function testReject()
    {
        $exception = new Exception;

        $this->emitter
            ->expects($this->once())
            ->method('emit')
            ->with(Event::MESSAGE_REJECT, $this->command, $exception);

        $this->event->reject($this->command, $exception);
    }
}
