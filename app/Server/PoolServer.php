<?php


namespace App\Server;

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

    public function bootstrap(): void
    {
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
            $this->protocol->handle(
                new ConnectionFd($conn), $this->engine
            );
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