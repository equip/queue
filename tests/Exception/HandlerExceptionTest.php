<?php

namespace Equip\Queue\Exception;

use Equip\Queue\TestCase;

class HandlerExceptionTest extends TestCase
{
    public function testInvalidHandler()
    {
        $exception = HandlerException::invalidHandler('test');

        $this->assertInstanceOf(HandlerException::class, $exception);
        $this->assertRegExp(
            '/The handler for `test` is invalid./',
            $exception->getMessage()
        );
    }
}
