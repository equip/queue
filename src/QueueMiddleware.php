<?php

namespace Equip\Queue;

use League\Tactician\Middleware;

class QueueMiddleware implements Middleware
{
    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var array
     */
    private $queue_map;

    /**
     * @param Queue $queue
     * @param array $queue_map
     */
    public function __construct(Queue $queue, array $queue_map = [])
    {
        $this->queue = $queue;
        $this->queue_map = $queue_map;
    }

    /**
     * @inheritdoc
     */
    public function execute($command, callable $next)
    {
        if ($command instanceof QueueableCommand) {
            $queue = $this->getQueue(get_class($command));

            return $this->queue->add(
                $queue,
                // Wraps the command so it isn't requeued
                new QueuedCommand($command)
            );
        }

        if ($command instanceof QueuedCommand) {
            $command = $command->command();
        }

        return $next($command);
    }

    /**
     * Gets the specific queue for the command
     *
     * @param string $command_name
     *
     * @return string
     *
     * @throws \Exception If the command isn't assigned to a pipe
     */
    private function getQueue($command_name)
    {
        // TODO: refactor this?
        foreach ($this->queue_map as $queue => $commands) {
            if (in_array($command_name, $commands)) {
                return $queue;
            }
        }

        // TODO: throw exception or just a generic queue?
        throw new \Exception('no queue belongs to that command');
    }
}
