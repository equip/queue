<?php

namespace Equip\Queue\Driver;

use Equip\Queue\Fake\Command;
use Equip\Queue\TestCase;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;

class BeanstalkDriverTest extends TestCase
{
    /**
     * @var Pheanstalk
     */
    private $beanstalk;

    /**
     * @var BeanstalkDriver
     */
    private $driver;

    protected function setUp()
    {
        $this->beanstalk = $this->createMock(Pheanstalk::class);
        $this->driver = new BeanstalkDriver($this->beanstalk);
    }

    public function testQueue()
    {
        $command = new Command;

        $this->beanstalk
            ->expects($this->once())
            ->method('useTube')
            ->with('test-queue')
            ->willReturn($this->beanstalk);

        $this->beanstalk
            ->expects($this->once())
            ->method('put')
            ->with(serialize($command))
            ->willReturn(true);

        $this->assertTrue($this->driver->enqueue('test-queue', $command));
    }

    public function testDequeue()
    {
        $command = new Command;
        $job = new Job(1, serialize($command));

        $this->beanstalk
            ->expects($this->once())
            ->method('watch')
            ->with('test-queue')
            ->willReturn($this->beanstalk);

        $this->beanstalk
            ->expects($this->once())
            ->method('ignore')
            ->with('default')
            ->willReturn($this->beanstalk);

        $this->beanstalk
            ->expects($this->once())
            ->method('reserve')
            ->with(5)
            ->willReturn($job);

        $this->assertEquals([
            unserialize($job->getData()),
            $job,
        ], $this->driver->dequeue('test-queue'));
    }

    public function testDequeueEmpty()
    {
        $this->beanstalk
            ->expects($this->once())
            ->method('watch')
            ->with('test-queue')
            ->willReturn($this->beanstalk);

        $this->beanstalk
            ->expects($this->once())
            ->method('ignore')
            ->with('default')
            ->willReturn($this->beanstalk);

        $this->beanstalk
            ->expects($this->once())
            ->method('reserve')
            ->with(5)
            ->willReturn(false);

        $this->assertEquals([
            false,
            false,
        ], $this->driver->dequeue('test-queue'));
    }

    public function testProcessed()
    {
        $job = new \stdClass;

        $this->beanstalk
            ->expects($this->once())
            ->method('delete')
            ->with($job)
            ->willReturn(true);

        $this->assertTrue($this->driver->processed($job));
    }
}
