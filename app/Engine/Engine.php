<?php


namespace App\Engine;


use App\Server\Request;
use App\Table\CacheInterface;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\WaitGroup;


class Engine implements EngineInterface
{
    /**
     * @var Processor
     */
    private $processor;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var WaitGroup
     */
    private $wg;
    /**
     * @var Channel
     */
    private $requestChan;
    /**
     * @var bool
     */
    private $isRunning = true;
    /**
     * ConcurrentEngine constructor.
     *
     * @param CacheInterface $cache
     * @param int $workerNum
     */
    public function __construct(CacheInterface $cache, int $workerNum)
    {
        $this->cache = $cache;
        $this->wg = new WaitGroup();
        $this->processor = new Processor();
        $this->requestChan = new Channel($workerNum);
    }

    /**
     * @param Request $request
     */
    public function submit(Request $request): void
    {
        $this->requestChan->push($request);
    }

    /**
     * Shutdown the Spider Engine.
     * Wait the all Coroutine end.
     */
    public function workerExit(): void
    {
        $this->isRunning = false;
        $this->requestChan->close();
        $this->wg->wait();
    }

    /**
     * Shutdown event.
     * Sync the Cache to the file.
     */
    public function shutdown(): void
    {
        $this->cache->sync();
    }

    /**
     * Start the Spider engine
     */
    public function run(): void
    {
        // Spider main logic
        go(function(){
            while ($this->isRunning) {
//                if ( $this->requestChan->isEmpty() ) {
//                    // @todo(wilon) If there is no request, Just Sleep(1) to yield
//                    Coroutine::sleep(1);
//                    continue;
//                }
                if (! $request = $this->requestChan->pop() ) {
                    echo 'Close'.PHP_EOL;
                    break; // close
                }
                $this->wg->add();
                go(function() use($request){
                    defer(function(){
                        $this->wg->done();
                    });
                    [$id, $data] = $this->processor->process($request);
                    $this->cache->put($id, $data);
                });
            }
        });
    }

    /**
     * @return mixed
     */
    public function pullOneResult()
    {
        return $this->cache->shift();
    }

    /**
     * @param string $key
     * @param array $data
     */
    public function putResult(string $key, array $data): void
    {
        $this->cache->put($key, $data);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function pullResult(string $key)
    {
        return $this->cache->pull($key);
    }
}