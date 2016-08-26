<?php

namespace Example;

use Equip\Queue\AbstractOptions;

class Options extends AbstractOptions
{
    protected $command = Command::class;
    protected $handler = 'handler';
    protected $queue = 'queue';
}
