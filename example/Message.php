<?php

namespace Example;

use Equip\Queue\AbstractMessage;

class Message extends AbstractMessage
{
    protected $command = Command::class;
    protected $handler = 'handler';
    protected $queue = 'queue';
}
