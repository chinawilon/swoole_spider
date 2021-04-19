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

/*    private $requestQ = [];
    private $workerQ = [];*/

    public function __construct()
    {
        $this->requestChan = new Channel();
        $this->workerChan = new Channel(100);
    }

    public function shutdown(): void
    {
        $this->isRunning = false;
    }

    public function run(): void
    {
        $this->isRunning = true;


        go(function () {
            for (;;) {
                $worker = $this->workerChan->pop();
                $request = $this->requestChan->pop();
                echo 'push'.PHP_EOL;
                $worker->push($request);
            }
            /*for (;;) {
                var_dump($this->requestQ);
                if ( $this->isRunning === false) {
                    break;
                }
                if (count($this->workerQ) > 0 && count($this->requestQ) > 0 ) {
                    $worker = array_shift($this->workerQ); // worker
                    $request = array_shift($this->requestQ); // request
                    var_dump($request);
                    $worker->push($request);
                }
                Coroutine::sleep(1);
            }*/
        });

        /*go(function () {
            for (;;) {
                if ( $this->isRunning === false ) {
                    break;
                }
                $this->workerQ[] = $this->workerChan->pop();
            }
        });

        go(function () {
            for (;;) {
                if ( $this->isRunning === false) {
                    break;
                }
                $this->requestQ[] = $this->requestChan->pop();
            }
        });*/
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