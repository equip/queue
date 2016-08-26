<?php

namespace Equip\Queue\Fake;

use Equip\Queue\AbstractOptions;

class Options extends AbstractOptions
{
    protected $command = 'example-command';
    protected $handler = 'example-handler';
    protected $queue = 'example-queue';
}
