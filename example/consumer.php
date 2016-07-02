<?php

require __DIR__ . '/../vendor/autoload.php';
require 'ExampleJob.php';

use Equip\Queue\Driver\RedisDriver;
use Equip\Queue\Worker;
use League\Event\Emitter;

$redis = new Redis;
$redis->connect('localhost');

$driver = new RedisDriver($redis);

$emitter = new Emitter;

$worker = new Worker(
    $driver,
    $emitter,
    ['name' => new ExampleJob]
);
$worker->consume();
