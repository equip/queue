<?php

require __DIR__ . '/../vendor/autoload.php';
require 'ExampleJob.php';

use Equip\Queue\Driver\RedisDriver;
use Equip\Queue\Worker;
use League\Event\Emitter;

$redis = new Redis;
$redis->connect('localhost');

$worker = new Worker(
    new RedisDriver($redis),
    new Emitter,
    ['name' => new ExampleJob]
);
$worker->consume('queue');
