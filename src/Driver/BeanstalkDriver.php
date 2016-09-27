<?php

namespace Equip\Queue\Driver;

use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;

class BeanstalkDriver implements DriverInterface
{
    /**
     * @var Pheanstalk
     */
    private $beanstalk;

    /**
     * @param Pheanstalk $beanstalk
     */
    public function __construct(Pheanstalk $beanstalk)
    {
        $this->beanstalk = $beanstalk;
    }

    /**
     * @inheritdoc
     */
    public function enqueue($queue, $command)
    {
        return (boolean) $this->beanstalk
            ->useTube($queue)
            ->put(serialize($command));
    }

    /**
     * @inheritdoc
     */
    public function dequeue($queue)
    {
        $job = $this->beanstalk
            ->watch($queue)
            ->ignore('default')
            ->reserve(5);

        $data = $job instanceof Job ? $job->getData() : $job;
        return [
            unserialize($data),
            $job,
        ];
    }

    /**
     * @inheritdoc
     */
    public function processed($job)
    {
        return (boolean) $this->beanstalk->delete($job);
    }
}
