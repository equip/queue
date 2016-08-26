<?php

namespace Equip\Queue;

use Equip\Queue\Fake\Command;
use Equip\Queue\Fake\Options;
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
    /**
     * @var AbstractOptions
     */
    private $options;

    protected function setUp()
    {
        $this->emitter = $this->createMock(EmitterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->event = new Event($this->emitter, $this->logger);
        $this->command = Command::class;
        $this->options = new Options;
    }

    public function testAcknowledge()
    {
        $this->emitter
            ->expects($this->exactly(2))
            ->method('emit')
            ->withConsecutive(
                [Event::MESSAGE_ACKNOWLEDGE, $this->options],
                [sprintf('%s.%s', Event::MESSAGE_ACKNOWLEDGE, $this->command), $this->options]
            );

        $this->event->acknowledge($this->command, $this->options);
    }

    public function testFinish()
    {
        $this->emitter
            ->expects($this->exactly(2))
            ->method('emit')
            ->withConsecutive(
                [Event::MESSAGE_FINISH, $this->options],
                [sprintf('%s.%s', Event::MESSAGE_FINISH, $this->command), $this->options]
            );

        $this->event->finish($this->command, $this->options);
    }

    public function testReject()
    {
        $exception = new Exception;

        $this->emitter
            ->expects($this->exactly(2))
            ->method('emit')
            ->withConsecutive(
                [Event::MESSAGE_REJECT, $this->options, $exception],
                [sprintf('%s.%s', Event::MESSAGE_REJECT, $this->command), $this->options, $exception]
            );

        $this->event->reject($this->command, $this->options, $exception);
    }

    public function testShutdown()
    {
        $this->emitter
            ->expects($this->exactly(2))
            ->method('emit')
            ->withConsecutive(
                [Event::QUEUE_SHUTDOWN],
                [sprintf('%s.%s', Event::QUEUE_SHUTDOWN, $this->command)]
            );

        $this->event->shutdown($this->command);
    }
}
