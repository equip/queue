<?php

namespace Example;

use Equip\Queue\QueueableCommand;

class ExampleCommand implements QueueableCommand
{
    private $foo = 'bar';

    public function foo()
    {
        return $this->foo;
    }
}
