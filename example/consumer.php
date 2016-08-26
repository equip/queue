<?php

require __DIR__ . '/../vendor/autoload.php';

use Equip\Queue\Driver\RedisDriver;
use Equip\Queue\Event;
use Equip\Queue\Handler\SimpleHandlerFactory;
use Equip\Queue\Worker;
use Example\ExampleJob;
use League\Event\Emitter;
use Monolog\Logger;

$redis = new Redis;
$redis->connect('localhost');

$worker = new Worker(
    new RedisDriver($redis),
    new Event(new Emitter),
    new Logger('queue'),
    new SimpleHandlerFactory([
        'handler' => ExampleJob::class,
    ])
);
$worker->consume('queue');
