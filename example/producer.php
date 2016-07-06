<?php

require __DIR__ . '/../vendor/autoload.php';

use Equip\Queue\Driver\RedisDriver;
use Equip\Queue\Message;
use Equip\Queue\Queue;

$redis = new Redis;
$redis->connect('localhost');

$driver = new RedisDriver($redis);

$queue = new Queue($driver);

$message = new Message(
    'queue',
    'handler',
    ['data' => 'value']
);

$result = $queue->add($message);

var_dump($result);
