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

##### Available Serializers
 - JSON

Missing the serializer you want? [Create it!](#creating-a-serializer)

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

// Instantiate the serializer class
$serializer = new Equip\Queue\Serializer\JsonSerializer;

// Create your handler routes
$routes = [
    // Any message within the queue with $message->handler() equal to 'awesome', will fire off this callable.
    'awesome' => function (Message $message) {
        var_dump($message);
    },
];

// Instantiate the Worker class
$worker = new Equip\Queue\Worker($driver, $event, $serializer, $routes);

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

// Instantiate the serializer class
$serializer = new Equip\Queue\Serializer\JsonSerializer;

// Instantiate the Queue class
$queue = new Queue($driver, $serializer);
```

Here's an [example producer](https://github.com/equip/queue/blob/master/example/producer.php)

### Creating A Message

Creating a message is as simple as instantiating an object:
```PHP
$message = new Equip\Queue\Message(
    // The queue this message will be pushed onto
    'queue',

    // The name of the handler that will be called when this message is being consumed
    'handler',

    // Message specific data that is used within your handler
    ['foo' => 'bar']
);
```

### Adding A Message To The Queue

After creating the [producer](#creating-a-producer), simply add your `Message` to the queue.
```PHP
$result = $queue->add($message);
```

A boolean (`$result`) is returned which contains the status of the push onto the queue.

### Creating A Handler

A handler is a [callable](http://php.net/manual/en/language.types.callable.php) that expects one parameter: `Equip\Queue\Message`.

Here's a couple examples of handlers:
```PHP
class ExampleHandler
{
    public function __invoke(Equip\Queue\Message $message) {
        var_dump($message);
    }
}

// Routes array that is passed into the consumer
$routes = [
    'example' => ExampleHandler::class,
    'foobar' => function (Equip\Queue\Message $message) {
        var_dump($message);
    },
];
```

If for some reason you want to stop the consumer after handling a message, you can return `false` from your handler.

### Creating A Driver

Creating a driver is as simple as implementing the [DriverInterface](https://github.com/equip/queue/blob/master/src/Driver/DriverInterface.php).

### Creating A Serializer

Creating a serializer is just as easy as creating a driver, just implement [MessageSerializerInterface](https://github.com/equip/queue/blob/master/src/Serializer/MessageSerializerInterface.php).
