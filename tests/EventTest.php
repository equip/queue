<?php

namespace Equip\Queue;

use Exception;
use League\Event\EmitterInterface;

class EventTest extends TestCase
{
    /**
     * @var EmitterInterface
     */
    private $emitter;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var AbstractOptions
     */
    private $message;

    protected function setUp()
    {
        $this->emitter = $this->createMock(EmitterInterface::class);
        $this->event = new Event($this->emitter);

        $this->message = $this->createMock(AbstractOptions::class);
        $this->message
            ->expects($this->exactly(2))
            ->method('handler')
            ->willReturn('example-handler');
    }

    public function testAcknowledge()
    {
        $this->emitter
            ->expects($this->exactly(2))
            ->method('emit')
            ->withConsecutive(
                [Event::MESSAGE_ACKNOWLEDGE, $this->message],
                [sprintf('%s.%s', Event::MESSAGE_ACKNOWLEDGE, $this->message->handler()), $this->message]
            );

        $this->event->acknowledge($this->message);
    }

    public function testFinish()
    {
        $this->emitter
            ->expects($this->exactly(2))
            ->method('emit')
            ->withConsecutive(
                [Event::MESSAGE_FINISH, $this->message],
                [sprintf('%s.%s', Event::MESSAGE_FINISH, $this->message->handler()), $this->message]
            );

        $this->event->finish($this->message);
    }

    public function testReject()
    {
        $exception = new Exception;

        $this->emitter
            ->expects($this->exactly(2))
            ->method('emit')
            ->withConsecutive(
                [Event::MESSAGE_REJECT, $this->message, $exception],
                [sprintf('%s.%s', Event::MESSAGE_REJECT, $this->message->handler()), $this->message, $exception]
            );

        $this->event->reject($this->message, $exception);
    }
}
