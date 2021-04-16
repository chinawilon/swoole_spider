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
     * @var ProtocolInterface
     */
    protected $protocol;
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
     * @param ProtocolInterface $protocol
     * @param EngineInterface $engine
     * @param null $workerNum
     */
    public function __construct(string $host, int $port, ProtocolInterface $protocol, EngineInterface $engine, CacheInterface $cache, $workerNum = null )
    {
        $this->host = $host;
        $this->port = $port;
        $this->protocol = $protocol;
        $this->engine = $engine;
        $this->cache = $cache;
        $this->workerNum = $workerNum;
        $this->bootstrap();
    }

    // Do something else
    abstract public function bootstrap(): void;

    // Start the server
    abstract public function start(): void ;
}