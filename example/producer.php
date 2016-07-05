<?php

require __DIR__ . '/../vendor/autoload.php';

use Equip\Queue\Driver\RedisDriver;
use Equip\Queue\Message\JsonMessage;
use Equip\Queue\Queue;

$redis = new Redis;
$redis->connect('localhost');

$driver = new RedisDriver($redis);

$queue = new Queue($driver);
$result = $queue->add(
    // Queue name
    'queue-name',

    // Name of the job
    'job-name',

    // Job data
    ['job-var-1' => 'job-value-1'],

    // Meta information related to the job
    ['meta-var-1' => 'meta-value-1']
);

var_dump($result);
