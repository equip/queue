<?php

namespace Equip\Queue\Router;

use Equip\Queue\Exception\HandlerException;
use Equip\Queue\Exception\RouterException;
use Equip\Queue\Fake\Handler;
use Equip\Queue\TestCase;

class SimpleRouteFactoryTest extends TestCase
{
    public function testNotFound()
    {
        $this->setExpectedExceptionRegExp(
            RouterException::class,
            '/Route not found for `test`./'
        );

        $router = new SimpleRouteFactory;
        $router->get('test');
    }

    public function testClassHandler()
    {
        $router = new SimpleRouteFactory([
            'foobar' => Handler::class,
        ]);

        $this->assertInstanceOf(Handler::class, $router->get('foobar'));
    }

    public function testClosureHandler()
    {
        $router = new SimpleRouteFactory([
            'foobar' => function () {
                return true;
            },
        ]);

        $callable = $router->get('foobar');
        $this->assertTrue(is_callable($callable));
        $this->assertTrue($callable());
    }

    public function testInvalidHandler()
    {
        $this->setExpectedExceptionRegExp(
            HandlerException::class,
            '/The handler for `foobar` is invalid./'
        );

        $router = new SimpleRouteFactory([
            'foobar' => 'foobar-test',
        ]);
        $router->get('foobar');
    }
}
