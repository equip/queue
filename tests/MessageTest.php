<?php

namespace Equip\Queue;

class MessageTest extends TestCase
{
    public function testQueue()
    {
        $message = new Message('queue', 'handler', ['foo' => 'bar']);
        $this->assertSame('queue', $message->queue());
    }

    public function testHandler()
    {
        $message = new Message('queue', 'handler', ['foo' => 'bar']);
        $this->assertSame('handler', $message->handler());
    }

    public function testData()
    {
        $message = new Message('queue', 'handler', ['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $message->data());
    }
}
