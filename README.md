# Equip Queue

[![Latest Stable Version](https://img.shields.io/packagist/v/equip/queue.svg)](https://packagist.org/packages/equip/queue)
[![License](https://img.shields.io/packagist/l/equip/queue.svg)](https://github.com/equip/queue/blob/master/LICENSE)
[![Build Status](https://travis-ci.org/equip/queue.svg)](https://travis-ci.org/equip/queue)
[![Code Coverage](https://scrutinizer-ci.com/g/equip/queue/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/equip/queue/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/equip/queue/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/equip/queue/?branch=master)

## Details

##### Available Drivers
 - Redis

Missing the driver you want? [Create it!](#creating-a-driver)

### Creating A Consumer

```PHP
// Instantiate Redis
$redis = new Redis;
$redis->connect('127.0.0.1');

// Instantiate the Redis driver
$driver = new Equip\Queue\Driver\RedisDriver($redis);

// Instantiate the event class (which uses league/event)
$emitter = new League\Event\Emitter;
$event = new Equip\Queue\Event($emitter);

// Instantiate the command factory
$injector = new Auryn\Injector;
$factory = new Equip\Queue\Command\AurynCommandFactory($injector);

// Instantiate the Worker class
$worker = new Equip\Queue\Worker($driver, $event, $factory);

// Kick off the consumer
$worker->consume($queue);
```

Here's an [example consumer](https://github.com/equip/queue/blob/master/example/consumer.php)

### Creating A Producer

```PHP
// Instantiate Redis
$redis = new Redis;
$redis->connect('127.0.0.1');

// Instantiate the Redis driver
$driver = new Equip\Queue\Driver\RedisDriver($redis);

// Instantiate the Queue class
$queue = new Queue($driver);
```

Here's an [example producer](https://github.com/equip/queue/blob/master/example/producer.php)

### Adding A Message To The Queue
```PHP
$result = $queue->add($queue, Command::class, new Options);
```

A boolean (`$result`) is returned which contains the status of the push onto the queue.

### Creating A Driver

Creating a driver is as simple as implementing the [DriverInterface](https://github.com/equip/queue/blob/master/src/Driver/DriverInterface.php).
