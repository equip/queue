<?php

require __DIR__ . '/../vendor/autoload.php';
require 'Job.php';

use Equip\Queue\Driver\RedisDriver;
use Equip\Queue\Event;
use Equip\Queue\Router\SimpleHandlerFactory;
use Equip\Queue\Serializer\JsonSerializer;
use Equip\Queue\Worker;
use League\Event\Emitter;
use Monolog\Logger;

$redis = new Redis;
$redis->connect('localhost');

$worker = new Worker(
    new RedisDriver($redis),
    new Event(new Emitter),
    new Logger('queue'),
    new JsonSerializer,
    new SimpleHandlerFactory([
        'handler' => ExampleJob::class,
    ])
);
$worker->consume('queue');
