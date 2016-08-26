<?php

require __DIR__ . '/../vendor/autoload.php';

use Equip\Queue\Driver\RedisDriver;
use Equip\Queue\Queue;
use Example\Options;

$redis = new Redis;
$redis->connect('localhost');

$queue = new Queue(
    new RedisDriver($redis)
);
$result = $queue->add(new Options());

var_dump($result);
