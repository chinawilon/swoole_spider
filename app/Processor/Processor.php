<?php


namespace App\Processor;


use App\Server\Request;
use App\Table\CacheInterface;
use Swoole\Coroutine\Http\Client;
use Throwable;


class Processor implements ProcessorInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param Request $request
     * @return mixed|string
     */
    public function process(Request $request)
    {
        $result = ['status_code' => 0];
        try {
            $http = new Client($request->getHost(), $request->getPort());
            $http->setMethod($request->getMethod());
            $http->setHeaders($request->getHeader());
            echo 'fetching url: '.$request->getMethod() .':'. $request->getUrl() . PHP_EOL;
            $http->execute($request->getPath() . '?' . $request->getQuery());
            $result['response_body'] = $http->getBody();
            $result['status_code'] = $http->getStatusCode();
            $http->close();
        } catch (Throwable $e) {
            $result['response_body'] = $e->getMessage();
        }
        $this->cache->put($request->getId(), $result);
    }
}