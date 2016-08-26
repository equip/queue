<?php

namespace Equip\Queue\Handler;

use Equip\Queue\Exception\HandlerException;
use Equip\Queue\Fake\Handler;
use Equip\Queue\TestCase;

class SimpleHandlerFactoryTest extends TestCase
{
    public function testNotFound()
    {
        $this->setExpectedExceptionRegExp(
            HandlerException::class,
            '/`test` handler not found./'
        );

        $factory = new SimpleCommandFactory;
        $factory->get('test');
    }

    public function testClassHandler()
    {
        $factory = new SimpleCommandFactory([
            'foobar' => Handler::class,
        ]);

        $this->assertInstanceOf(Handler::class, $factory->get('foobar'));
    }

    public function testClosureHandler()
    {
        $factory = new SimpleCommandFactory([
            'foobar' => function () {
                return true;
            },
        ]);

        $callable = $factory->get('foobar');
        $this->assertTrue(is_callable($callable));
        $this->assertTrue($callable());
    }

    public function testInvalidHandler()
    {
        $this->setExpectedExceptionRegExp(
            HandlerException::class,
            '/The handler for `foobar` is invalid./'
        );

        $factory = new SimpleCommandFactory([
            'foobar' => 'foobar-test',
        ]);
        $factory->get('foobar');
    }
}
