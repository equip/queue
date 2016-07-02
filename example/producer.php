<?php

require __DIR__ . '/../vendor/autoload.php';

use Equip\Queue\Driver\RedisDriver;
use Equip\Queue\Queue;

$redis = new Redis;
$redis->connect('localhost');

$driver = new RedisDriver($redis);

$queue = new Queue($driver);
$result = $queue->add('name', ['test' => 'example']);

var_dump($result);
