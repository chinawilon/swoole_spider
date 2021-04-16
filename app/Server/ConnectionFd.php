<?php


namespace App\Server;


use App\Exceptions\SpiderException;
use Swoole\Coroutine\Server\Connection;

class ConnectionFd implements FdInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string
     * @throws SpiderException
     */
    public function read(): string
    {
        $data = $this->connection->recv();
        if ( $data === '' || $data === false ) {
            $errCode = swoole_last_error();
            $errMsg = socket_strerror($errCode);
            $this->connection->close();
            throw new SpiderException("errCode: {$errCode}, errMsg: {$errMsg}");
        }
        return $data;
    }

    public function write(string $msg): void
    {
        $this->connection->send($msg);
    }
}