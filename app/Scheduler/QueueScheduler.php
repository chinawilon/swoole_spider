<?php


namespace App\Scheduler;


use App\Server\Request;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;


class QueueScheduler implements SchedulerInterface
{

    /**
     * @var Channel
     */
    private $requestChan;

    /**
     * @var Channel
     */
    private $workerChan;
    /**
     * @var bool
     */
    private $isRunning = false;

    public function __construct()
    {
        $this->requestChan = new Channel();
        $this->workerChan = new Channel();
    }

    public function shutdown(): void
    {
        $this->isRunning = false;
    }

    public function run(): void
    {
        $this->isRunning = true;
        
        $workerQ  = [];
        $requestQ = [];

        go(function () use(&$workerQ, &$requestQ) {
            for (;;) {
                if ( $this->isRunning === false) {
                    break;
                }
                if (count($workerQ) > 0 && count($requestQ) > 0 ) {
                    $worker = array_shift($workerQ); // worker
                    $request = array_shift($requestQ); // request
                    $worker->push($request);
                }
                Coroutine::sleep(1);
            }
        });

        go(function () use(&$workerQ) {
            for (;;) {
                if ( $this->isRunning === false ) {
                    break;
                }
                $workerQ[] = $this->workerChan->pop();
            }
        });

        go(function () use(&$requestQ){
            for (;;) {
                if ( $this->isRunning === false) {
                    break;
                }
                $requestQ[] = $this->requestChan->pop();
            }
        });
    }

    public function workerReady(Channel $worker): void
    {
        $this->workerChan->push($worker);
    }

    public function submit(Request $request): void
    {
        $this->requestChan->push($request);
    }

    public function workerChan(): Channel
    {
        return new Channel();
    }
}