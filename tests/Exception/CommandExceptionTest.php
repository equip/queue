<?php

namespace Equip\Queue\Exception;

use Equip\Queue\TestCase;

class CommandExceptionTest extends TestCase
{
    public function testInvalidCommand()
    {
        $exception = CommandException::invalidCommand('test');

        $this->assertInstanceOf(CommandException::class, $exception);
        $this->assertSame('The command for `test` is invalid.', $exception->getMessage());
    }
}
