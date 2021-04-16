<?php


namespace App\Server;


use Swoole\Server;

class ReceiveFd implements FdInterface
{
    /**
     * @var string
     */
    private $data;
    private $server;
    private $fd;
    private $socket;

    public function __construct(Server $server, $fd, $socket, string $data)
    {
        $this->server = $server;
        $this->fd = $fd;
        $this->socket = $socket;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function read(): string
    {
        return $this->data;
    }

    public function write(string $msg): void
    {
        $this->server->send($this->fd, $msg, $this->socket);
    }
}