<?php

namespace Equip\Queue;

use Eloquent\Liberator\Liberator;
use Eloquent\Phony\Phpunit\Phony;
use Equip\Queue\Fake\Command;
use Equip\Queue\Fake\QueueableCommand;

class QueueMiddlewareTest extends TestCase
{
    private $queue;

    protected function setUp()
    {
        $this->queue = Phony::mock(Queue::class);
    }

    public function testGetQueue()
    {
        $result = $this->middleware()->getQueue(Command::class);

        $this->assertSame('test', $result);
    }

    public function testGetQueueNone()
    {
        $this->setExpectedExceptionRegExp(
            \Exception::class,
            '/No queue has been set for the `TestClass` command/'
        );

        $this->middleware()->getQueue('TestClass');
    }

    public function testExecuteNormal()
    {
        $result = $this->middleware()->execute(new Command, function (Command $command) {
            return $command->foo();
        });

        $this->assertSame('bar', $result);
    }

    public function testExecuteQueuing()
    {
        // Mock
        $command = new QueueableCommand;
        $this->queue->add->returns(true);

        // Execute
        $result = $this->middleware()->execute($command, function (QueueableCommand $command) {
            return $command->bar();
        });

        // Verify
        $this->queue->add->calledWith('foobar', new QueuedCommand($command));
        $this->assertTrue($result);
    }

    public function testExecuteQueued()
    {
        $command = new QueuedCommand(new Command());

        $result = $this->middleware()->execute($command, function (Command $command) {
            return $command->foo();
        });

        $this->assertSame('bar', $result);
    }

    private function middleware(array $map = [])
    {
        if (empty($map)) {
            $map = [
                'test' => [
                    Command::class,
                ],
                'foobar' => [
                    QueueableCommand::class,
                ],
            ];
        }

        $middleware = new QueueMiddleware(
            $this->queue->get(),
            $map
        );

        return Liberator::liberate($middleware);
    }
}
