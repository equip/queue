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
        $message = ['name' => 'job-name'];

        $this->emitter
            ->expects($this->exactly(2))
            ->method('emit')
            ->withConsecutive(
                [Event::QUEUE_ACKNOWLEDGE, $message],
                [sprintf('%s.%s', Event::QUEUE_ACKNOWLEDGE, $message['name']), $message]
            );

        $this->event->acknowledge($message);
    }

    public function testReject()
    {
        $message = ['name' => 'job-name'];
        $exception = new Exception;

        $this->emitter
            ->expects($this->exactly(2))
            ->method('emit')
            ->withConsecutive(
                [Event::QUEUE_REJECT, $message, $exception],
                [sprintf('%s.%s', Event::QUEUE_REJECT, $message['name']), $message, $exception]
            );

        $this->event->reject($message, $exception);
    }
}
