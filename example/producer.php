<?php

require __DIR__ . '/../vendor/autoload.php';

use Equip\Queue\Driver\RedisDriver;
use Equip\Queue\Queue;
use Example\ExampleCommand;
use Example\ExampleOptions;

$redis = new Redis;
$redis->connect('localhost');

$queue = new Queue(
    new RedisDriver($redis)
);
$result = $queue->add('example-queue', ExampleCommand::class, new ExampleOptions);

var_dump($result);
