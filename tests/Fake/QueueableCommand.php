<?php

namespace Equip\Queue\Fake;

class QueueableCommand implements \Equip\Queue\QueueableCommand
{
    private $bar = 'foo';

    public function bar()
    {
        return $this->bar;
    }
}
