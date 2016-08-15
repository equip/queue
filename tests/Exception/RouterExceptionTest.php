<?php

namespace Equip\Queue\Exception;

use Equip\Queue\TestCase;

class RouterExceptionTest extends TestCase
{
    public function testNotFound()
    {
        $exception = RouterException::routeNotFound('test');

        $this->assertInstanceof(RouterException::class, $exception);
        $this->assertSame('Route not found for `test`.', $exception->getMessage());
    }
}
