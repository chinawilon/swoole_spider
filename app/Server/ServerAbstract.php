<?php


namespace App\Server;


use App\Engine\EngineInterface;
use App\Table\CacheInterface;

abstract class ServerAbstract
{
    /**
     * @var string
     */
    protected $host;
    /**
     * @var int
     */
    protected $port;
    /**
     * @var EngineInterface
     */
    protected $engine;
    /**
     * @var null
     */
    protected $workerNum;
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * ServerAbstract constructor.
     *
     * @param string $host
     * @param int $port
     * @param EngineInterface $engine
     * @param null $workerNum
     */
    public function __construct(string $host, int $port, EngineInterface $engine, $workerNum = null )
    {
        $this->host = $host;
        $this->port = $port;
        $this->engine = $engine;
        $this->workerNum = $workerNum ?? swoole_cpu_num();
        $this->bootstrap();
    }

    // Do something else
    abstract public function bootstrap(): void;

    // Start the server
    abstract public function start(): void ;
}