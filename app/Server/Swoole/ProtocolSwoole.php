<?php


namespace App\Server\Swoole;


use App\Server\ProtocolAbstract;
use App\Server\Request;
use JsonException;
use Swoole\Coroutine;
use Swoole\Server;

class ProtocolSwoole extends ProtocolAbstract
{
    /**
     * @var string
     */
    private $left = '';

    /**
     * @var string
     */
    private $type = '';

    /**
     * @param Server $server
     * @param $fd
     * @param $data
     * @throws JsonException
     */
    public function receive(Server $server, $fd, $data): void
    {
        $this->left .= $data;
        if ( $this->type === '' ) {
            if ( $this->getLeftLen() < self::TYPE_LENGTH ) {
                return;
            }
            $this->type = substr($this->left, 0, self::TYPE_LENGTH);
            $this->left = substr($this->left, self::TYPE_LENGTH);
        }
        switch ($this->type) {
            case self::TYPE_PUB:
                    $this->publish($server, $fd);
                break;
            case self::TYPE_SUB:
                    $this->subscribe($server, $fd);
                break;
            default:
                return;
        }
    }

    /**
     * @return int
     */
    public function getLeftLen():int
    {
        return strlen($this->left);
    }

    /**
     * @param Server $server
     * @param $fd
     */
    public function publish(Server $server, $fd): void
    {
        if ($this->getLeftLen() < self::DATA_LENGTH) {
            return;
        }
        [, $length] = unpack('N', substr($this->left, 0, self::DATA_LENGTH));
        $contentLength = $length + self::DATA_LENGTH;
        if ( $this->getLeftLen() >= $contentLength) {
            $request = substr($this->left, self::DATA_LENGTH, $length);
            go(function() use($server, $fd, $request) {
                if ($server->send($fd, $uid = $this->atomic->add()) ) {
                    // If not send success. drop it??
                    $this->engine->submit($uid, $request);
                }
            });
            $this->left = substr($this->left, $contentLength);
        }
    }

    /**
     * @param Server $server
     * @param $fd
     * @throws JsonException
     */
    public function subscribe(Server $server, $fd): void
    {
        for (;;) {
            if (! $server->exist($fd) ) {
                break;
            }
            if ( $result = $this->engine->pullOneResult() ) {
                $msg = json_encode($result, JSON_THROW_ON_ERROR);
                if (! $server->send($fd, pack('N', strlen($msg)) . $msg) ) {
                    $this->engine->putResult($result['id'], $result); // save it back !!
                    break;
                }
            } else {
                // @fixme(wilon) If no result to send, just sleep(1) to yield
                Coroutine::sleep(1);
            }
        }
    }
}
