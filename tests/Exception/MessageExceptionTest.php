<?php

namespace Equip\Queue\Exception;

use Equip\Queue\TestCase;

class MessageExceptionTest extends TestCase
{
    public function testMissingProperty()
    {
        $exception = MessageException::missingProperty('test');

        $this->assertInstanceof(MessageException::class, $exception);
        $this->assertSame('Missing property `test`.', $exception->getMessage());
    }
}
