<?php

namespace Equip\Queue\Exception;

use Equip\Queue\TestCase;

class HandlerExceptionTest extends TestCase
{
    public function testInvalidHandler()
    {
        $exception = HandlerException::invalidHandler('test');

        $this->assertInstanceOf(HandlerException::class, $exception);
        $this->assertSame('The handler for `test` is invalid.', $exception->getMessage());
    }

    public function testNotFound()
    {
        $exception = HandlerException::notFound('foobar');

        $this->assertInstanceOf(HandlerException::class, $exception);
        $this->assertSame('`foobar` handler not found.', $exception->getMessage());
    }
}
