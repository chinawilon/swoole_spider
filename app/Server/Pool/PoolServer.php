<?php


namespace App\Server\Pool;

use App\Server\ServerAbstract;
use Co\Server\Connection;
use Swoole\Coroutine\Server;
use Swoole\Exception;
use Swoole\Process;
use Swoole\Process\Pool;

class PoolServer extends ServerAbstract
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var ProtocolPool
     */
    private $protocol;

    public function bootstrap(): void
    {
        $this->protocol = new ProtocolPool($this->engine);
        $this->pool = new Pool($this->workerNum ?? swoole_cpu_num());
        $this->pool->set(['enable_coroutine' => true]);
        $this->pool->on('workerStart', [$this, 'handle']);
    }

    /**
     * Handle the connection
     *
     * @throws Exception
     */
    public function handle(): void
    {
        // Start the spider engine
        $this->engine->run();

        // Request server
        $server = new Server($this->host, $this->port, false, true);
        Process::signal(SIGTERM, function () use($server)  {
            $server->shutdown();
            $this->engine->shutdown();
        });

        // some protocol the handle the connection
        $server->handle(function (Connection $conn) {
            $this->protocol->handle($conn);
        });
        $server->start();
    }

    /**
     * Run the server
     */
    public function start(): void
    {
        $this->pool->start();
    }
}