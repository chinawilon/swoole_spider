<?php


namespace App\Engine;


use App\Server\Request;
use App\Table\RequestCache;
use App\Table\ResponseCache;
use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;


class Engine implements EngineInterface
{
    /**
     * @var Processor
     */
    private $processor;
    /**
     * @var WaitGroup
     */
    private $wg;
    /**
     * @var ResponseCache
     */
    private $responseCache;
    /**
     * @var RequestCache
     */
    private $requestCache;

    /**
     * @var bool
     */
    private $isRunning = true;


    public function __construct(ResponseCache $responseCache, RequestCache $requestCache)
    {
        $this->responseCache = $responseCache;
        $this->requestCache =  $requestCache;
        $this->wg = new WaitGroup();
        $this->processor = new Processor();
    }

    /**
     * @param string $key
     * @param string $request
     */
    public function submit(string $key, string $request): void
    {
        $data = ['data' => $request, 'id' => $key];
        $this->requestCache->put($key, $data);
    }

    /**
     * Shutdown the Spider Engine.
     * Wait the all Coroutine end.
     */
    public function workerStop(): void
    {
        $this->isRunning = false;
        $this->wg->wait();
    }

    /**
     * Shutdown event.
     * Sync the CacheAbstract to the file.
     */
    public function shutdown(): void
    {
        $this->responseCache->sync();
        $this->requestCache->sync();
    }

    /**
     * Start the Spider engine
     */
    public function run(): void
    {
        // Spider main logic
        go(function(){
            while ($this->isRunning) {
                if ( $data = $this->requestCache->shift() ) {
                    $this->wg->add();
                    go(function() use($data){
                        defer(function(){
                            $this->wg->done();
                        });
                        $request = json_decode($data['data'], true, 512, JSON_THROW_ON_ERROR);
                        [$id, $data] = $this->processor->process(
                            new Request($data['id'], $request)
                        );
                        $this->responseCache->put($id, $data);
                    });
                } else {
                    // @fixme(wilon) If there is no request, just sleep(1) for yield
                    Coroutine::sleep(1);
                }
            }
        });
    }

    /**
     * @return mixed
     */
    public function pullOneResult()
    {
        return $this->responseCache->shift();
    }

    /**
     * @param string $key
     * @param array $data
     */
    public function putResult(string $key, array $data): void
    {
        $this->responseCache->put($key, $data);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function pullResult(string $key)
    {
        return $this->responseCache->pull($key);
    }
}