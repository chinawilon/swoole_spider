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
        $result = ['status_code' => 0, 'id'=>$request->getId()];
        $http = new Client($request->getHost(), $request->getPort());
        defer(static function () use($http) {
            $http->close();
        });
        try {
            $http->setMethod($request->getMethod());
            $http->setHeaders( $request->getHeader() ?? []);
            $http->setData($request->getBody());
            $http->set(['timeout'=>$request->getTimeout()]);
            echo 'fetching url: '.$request->getMethod() .':'. $request->getUrl() . PHP_EOL;
            $http->execute($request->getPath() . '?' . $request->getQuery());
            $result['response_body'] = $http->getBody();
            $result['status_code'] = $http->getStatusCode();

        } catch (Throwable $e) {
            $result['response_body'] = $e->getMessage();
        }
        $this->cache->put($request->getId(), $result);
    }
}