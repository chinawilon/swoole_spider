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
        $this->server->set(['task_enable_coroutine' => true]);
        $this->server->set(['hook_flags' => SWOOLE_HOOK_ALL]);
        $this->server->on('WorkerStart', [$this, 'handle']);
        $this->server->on('WorkerStop', [$this, 'shutdown']);
        // Socket handle
        $this->socketHandle();
    }

    /**
     * Shutdown the Server
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