<?php

namespace Example;

use Equip\Queue\AbstractOptions;

class ExampleOptions extends AbstractOptions
{
    protected $command = ExampleCommand::class;
    protected $queue = 'queue';
}
