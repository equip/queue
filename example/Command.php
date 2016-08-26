<?php

namespace Example;

use Equip\Command\CommandInterface;

class Command implements CommandInterface
{
    public function execute()
    {
        var_dump('test');
        exit;
    }
}
