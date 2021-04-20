<?php


namespace App\Server\Pool;

use Co\Server\Connection;

class BuffIO
{
    /**
     * @var string
     */
    private $left = "";

    /**
     * @var Connection
     */
    private $conn;


    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @return bool
     */
    public function isLive(): bool
    {
        $socket = $this->conn->exportSocket();
        return $socket->checkLiveness();
    }

    /**
     * @param string $send
     */
    public function write(string $send): void
    {
        $this->left .= $send;
    }

    /**
     * flush the buffer
     */
    public function flush(): void
    {
        $msg = $this->left;
        $this->left = '';
        $this->conn->send($msg);
    }

    /**
     * @param $what
     * @return false|string
     */
    public function read($what)
    {
        for (;;) {
            if ( strlen($this->left) < $what ) {
                if (! $data = $this->readNextMsg() ) {
                    break;
                }
                $this->left .= $data;
                continue ;
            }
            $ret = substr($this->left, 0, $what);
            $this->left = substr($this->left, $what);
            return $ret;
        }
    }

    /**
     * @return string
     */
    public function readNextMsg(): string
    {
        $data = $this->conn->recv();
        if ( $data === '' || $data === false ) {
            $this->conn->close();
        }
        return $data;
    }



}