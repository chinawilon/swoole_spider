<?php


namespace App\Server;

use Swoole\Atomic;
use Swoole\Server;

class SwooleServer extends ServerAbstract
{
    /**
     * @var Server
     */
    private $server;
    /**
     * @var Atomic
     */
    private $atomic;

    public function bootstrap(): void
    {
        $this->server = new Server($this->host, $this->port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $this->server->set(['task_enable_coroutine' => true]);
        $this->server->set(['hook_flags' => SWOOLE_HOOK_ALL]);
        $this->server->on('WorkerStart', [$this, 'handle']);
        // Socket handle
        $this->socketHandle();
        $this->setAtomic();
    }

    /**
     * UID generator
     *
     * Maybe shutdown. Use the Now(timestamp) for the start ID
     */
    public function setAtomic(): void
    {
        $this->atomic = new Atomic(time());
    }

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
            $onReceives[$fd] = $this->getOnReceive();
        });

        // Socket Close
        $this->server->on('close', function ($server, $fd, $fid) use(&$onReceives) {
            unset($onReceives[$fd]);
        });

        // Socket Receive
        $this->server->on('receive', function (Server $server, $fd, $rid, $data) use(&$onReceives) {
            if (isset($onReceives[$fd])) {
                $onReceive = $onReceives[$fd];
                $onReceive($server, $fd, $data);
            }
            else {
                $server->close($fd, true);
            }
        });
    }

    /**
     * Handle the Receive Event
     *
     * @return callable
     */
    public function getOnReceive(): callable
    {
        $left = '';
        $type = '';
        $typeLength = 3;
        $dataLength = 2;
        return function (Server $server, $fd, $data) use(&$left, &$type, $typeLength, $dataLength) {
            $left .= $data;
            while (true) {
                if ( $type === '' ) {
                    if ( strlen($left) < $typeLength ) {
                        break;
                    }
                    $type = substr($left, 0, $typeLength);
                    $left = substr($left, $typeLength);
                }
                switch ($type) {
                    case 'PUB':
                        if (strlen($left) < $dataLength) {
                            break 2;
                        }
                        [, $length] = unpack('n', substr($left, 0, $dataLength));
                        $contentLength = $length + $dataLength;
                        if ( strlen($left) >= $contentLength) {
                            $data = substr($left, $dataLength, $length);
                            $request = json_decode($data, true, 512, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                            $server->send($fd, $uid = $this->atomic->add()); // return the uid
                            $this->engine->submit(new Request($uid, $request));
                            $left = substr($left,  $contentLength);
                        } else {
                            break 2;
                        }
                        break;
                    case 'SUB':
                        while (true) {
                            if ( $result = $this->cache->shift() ) {
                                echo $result;
                                $send = json_encode($result, JSON_THROW_ON_ERROR);
                                echo strlen($send).PHP_EOL;
                                $server->send($fd, pack('n', strlen($send)).$send);
                                usleep(1); // yield
                            } else {
                                $server->close($fd); // close the fd
                                break 3;
                            }
                        }
                        break;
                }
            }
        };
    }


    public function start(): void
    {
        $this->server->start();
    }
}