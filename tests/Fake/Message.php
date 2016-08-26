<?php

namespace Equip\Queue\Fake;

use Equip\Queue\AbstractMessage;

class Message extends AbstractMessage
{
    protected $command = 'example-command';
    protected $handler = 'example-handler';
    protected $queue = 'example-queue';
}
