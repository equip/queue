<?php

namespace Equip\Queue\Fake;

use Equip\Command\CommandImmutableOptionsTrait;
use Equip\Command\CommandInterface;
use Equip\Command\OptionsInterface;

class Command
{
    private $foo = 'bar';

    public function foo()
    {
        return $this->foo;
    }
}
