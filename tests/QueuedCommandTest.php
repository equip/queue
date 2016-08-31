<?php

namespace Equip\Queue;

use Equip\Queue\Fake\Command;

class QueuedCommandTest extends TestCase
{
    public function testCommand()
    {
        $command = new Command;
        $queued_command = new QueuedCommand($command);

        $this->assertSame($command, $queued_command->command());
    }
}
