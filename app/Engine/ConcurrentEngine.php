<?php


namespace App\Engine;


use App\Processor\ProcessorInterface;
use App\Server\Request;
use App\Scheduler\SchedulerInterface;
use Swoole\Coroutine\Channel;

class ConcurrentEngine implements EngineInterface
{
    /**
     * @var int
     */
    private $workerCount;

    /**
     * @var SchedulerInterface
     */
    private $scheduler;

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var bool
     */
    private $isRunning = false;


    /**
     * ConcurrentEngine constructor.
     *
     * @param SchedulerInterface $scheduler
     * @param ProcessorInterface $processor
     * @param $workerCount
     */
    public function __construct(SchedulerInterface $scheduler, ProcessorInterface $processor, $workerCount)
    {
        $this->scheduler = $scheduler;
        $this->workerCount = $workerCount;
        $this->processor = $processor;
    }

    /**
     * @param Request $request
     */
    public function submit(Request $request): void
    {
        $this->scheduler->submit($request);
    }

    /**
     * Shutdown the Engine
     */
    public function shutdown(): void
    {
        $this->isRunning = false;
        $this->scheduler->shutdown();
    }

    /**
     * Run the spider
     */
    public function run(): void
    {
        $this->scheduler->run();
        $this->isRunning = true;
        for ($i = 0; $i < $this->workerCount; $i++) {
            $this->createWorker($this->scheduler->workerChan());
        }
    }

    /**
     * @param Channel $in
     */
    public function createWorker(Channel $in): void
    {
        go(function () use($in) {
            for (;;) {
                if ( $this->isRunning === false ) {
                    break; // shutdown
                }
                $this->scheduler->workerReady($in);
                $request = $in->pop();
                $this->processor->process($request);
            }
        });
    }

}