<?php

namespace Equip\Queue\Command;

use Auryn\Injector;
use Equip\Queue\Exception\CommandException;
use Equip\Queue\Fake\Command;
use Equip\Queue\TestCase;

class AurynCommandFactoryTest extends TestCase
{
    /**
     * @var Injector
     */
    private $injector;

    /**
     * @var AurynCommandFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->injector = $this->createMock(Injector::class);
        $this->factory = new AurynCommandFactory($this->injector);
    }

    public function testNotFound()
    {
        $this->setExpectedExceptionRegExp(
            CommandException::class,
            '/`test` command not found./',
            CommandException::NOT_FOUND
        );

        $this->factory->make('test');
    }

    public function testInvalid()
    {
        $this->setExpectedExceptionRegExp(
            CommandException::class,
            '/The command for `.*` is invalid./',
            CommandException::INVALID
        );

        $this->factory->make(get_class($this));
    }

    public function testMake()
    {
        $command = new Command;

        $this->injector
            ->expects($this->once())
            ->method('make')
            ->with(Command::class)
            ->willReturn($command);

        $this->assertSame($command, $this->factory->make(Command::class));
    }
}
