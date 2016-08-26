<?php

require __DIR__ . '/../vendor/autoload.php';

use Equip\Queue\Driver\RedisDriver;
use Equip\Queue\Event;
use Equip\Queue\Command\SimpleCommandFactory;
use Equip\Queue\Worker;
use League\Event\Emitter;
use Monolog\Logger;

$redis = new Redis;
$redis->connect('localhost');

$worker = new Worker(
    new RedisDriver($redis),
    new Event(new Emitter),
    new Logger('queue'),
    new SimpleCommandFactory
);
$worker->consume('queue');
