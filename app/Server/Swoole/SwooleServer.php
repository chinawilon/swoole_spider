<?php


namespace App\Server\Swoole;

use App\Server\ServerAbstract;
use Swoole\Server;

class SwooleServer extends ServerAbstract
{
    /**
     * @var Server
     */
    private $server;

    /**
     * Bootstrap
     */
    public function bootstrap(): void
    {
        $this->server = new Server($this->host, $this->port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $this->server->set([
            'task_enable_coroutine' => true,
            'reload_async' => true,
            'hook_flags' => SWOOLE_HOOK_ALL,
            'worker_num' => $this->workerNum,
        ]);
        $this->server->on('WorkerStart', [$this, 'handle']);
        $this->server->on('WorkerExit', [$this, 'workerExit']);
        // $this->server->on('WorkerStop', [$this, 'workerStop']);
        $this->server->on('Shutdown', [$this, 'shutdown']);
        // Socket handle
        $this->socketHandle();
    }

    /**
     * Worker exit event
     */
    public function workerExit(): void
    {
        $this->engine->workerStop();
    }

    /**
     * Shutdown stop event
     */
    public function shutdown(): void
    {
        $this->engine->shutdown();
    }

    /**
     * Handle the Spider engine
     */
    public function handle(): void
    {
        $this->engine->run();
    }

    /**
     * Handle the Socket Event
     */
    public function socketHandle(): void
    {
        // Socket Connect
        $onReceives = array();
        $this->server->on('connect', function ($server, $fd, $rid) use(&$onReceives){
            $onReceives[$fd] = new ProtocolSwoole($this->engine);
        });

        // Socket Close
        $this->server->on('close', function ($server, $fd, $fid) use(&$onReceives) {
            unset($onReceives[$fd]);
        });

        // Socket Receive
        $this->server->on('receive', function (Server $server, $fd, $rid, $data) use(&$onReceives) {
            if (isset($onReceives[$fd])) {
                /**@var $protocol ProtocolSwoole **/
                $protocol = $onReceives[$fd];
                $protocol->receive($server, $fd, $data);
            }
            else {
                $server->close($fd, true);
            }
        });
    }

    public function start(): void
    {
        $this->server->start();
    }
}