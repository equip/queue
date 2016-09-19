<?php

require __DIR__ . '/../vendor/autoload.php';

// Setup Queue

$redis = new Redis();
$redis->connect('localhost');
$driver = new \Equip\Queue\Driver\RedisDriver($redis);
$queue = new \Equip\Queue\Queue($driver);

$queue_middleware = new \Equip\Queue\QueueMiddleware(
    $queue,
    [
        'test' => [
            \Example\ExampleCommand::class,
        ],
    ]
);

// Setup Tactician

$locator = new \League\Tactician\Handler\Locator\InMemoryLocator([
    \Example\ExampleCommand::class => new \Example\ExampleHandler(),
]);

$handler_middleware = new \League\Tactician\Handler\CommandHandlerMiddleware(
    new \League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor(),
    $locator,
    new \League\Tactician\Handler\MethodNameInflector\HandleInflector()
);

$bus = new \League\Tactician\CommandBus([
    $queue_middleware,
    $handler_middleware,
]);

// Setup worker
$worker = new \Equip\Queue\Worker(
    $driver,
    $queue,
    new \Equip\Queue\Event(
        new \League\Event\Emitter(),
        new \Monolog\Logger('queue')
    ),
    $bus
);

if ($argv[1] == 'produce') {
    $bus->handle(new \Example\ExampleCommand());
} else {
    $worker->consume('test');
}
