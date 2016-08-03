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

    protected function setUp()
    {
        $this->emitter = $this->createMock(EmitterInterface::class);
        $this->event = new Event($this->emitter);
    }

    public function testAcknowledge()
    {
        $message = new Message(
            'queue',
            'handler',
            ['foo' => 'bar']
        );

        $this->emitter
            ->expects($this->exactly(2))
            ->method('emit')
            ->withConsecutive(
                [Event::MESSAGE_ACKNOWLEDGE, $message],
                [sprintf('%s.%s', Event::MESSAGE_ACKNOWLEDGE, $message->handler()), $message]
            );

        $this->event->acknowledge($message);
    }

    public function testFinish()
    {
        $message = new Message(
            'queue',
            'handler',
            ['foo' => 'bar']
        );

        $this->emitter
            ->expects($this->exactly(2))
            ->method('emit')
            ->withConsecutive(
                [Event::MESSAGE_FINISH, $message],
                [sprintf('%s.%s', Event::MESSAGE_FINISH, $message->handler()), $message]
            );

        $this->event->finish($message);
    }

    public function testReject()
    {
        $message = new Message(
            'queue',
            'handler',
            ['foo' => 'bar']
        );

        $exception = new Exception;

        $this->emitter
            ->expects($this->exactly(2))
            ->method('emit')
            ->withConsecutive(
                [Event::MESSAGE_REJECT, $message, $exception],
                [sprintf('%s.%s', Event::MESSAGE_REJECT, $message->handler()), $message, $exception]
            );

        $this->event->reject($message, $exception);
    }
}
