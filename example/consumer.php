<?php

require __DIR__ . '/../vendor/autoload.php';

use Auryn\Injector;
use Equip\Queue\Command\AurynCommandFactory;
use Equip\Queue\Driver\RedisDriver;
use Equip\Queue\Event;
use Equip\Queue\Worker;
use League\Event\Emitter;
use Monolog\Logger;

$redis = new Redis;
$redis->connect('localhost');

$worker = new Worker(
    new RedisDriver($redis),
    new Event(new Emitter, new Logger('queue')),
    new AurynCommandFactory(new Injector)
);
$worker->consume('example-queue');
