<?php


namespace App\Server\Pool;

use App\Server\ProtocolAbstract;
use Co\Server\Connection;
use JsonException;
use Swoole\Coroutine;

class ProtocolPool extends ProtocolAbstract
{

    /**
     * @param Connection $conn
     * @throws JsonException
     */
    public function handle(Connection $conn): void
    {
        $writer = new BuffIO($conn);
        $reader = new BuffIO($conn);
        $type = $reader->read(self::TYPE_LENGTH);
        switch ($type) {
            case self::TYPE_PUB:
                $this->publish($reader, $writer);
                break;
            case self::TYPE_SUB:
                $this->subscribe($writer, $reader);
                break;
            default:
                $writer->write("403 Forbidden");
                $writer->flush();
        }
    }

    /**
     * @param BuffIO $reader
     * @param BuffIO $writer
     */
    public function publish(BuffIO $reader, BuffIO $writer): void
    {
        while (true) {
            if (! $data = $reader->read(self::DATA_LENGTH) ) {
                break;
            }
            [, $length] = unpack('N', $data);
            if (! $data = $reader->read($length) ) {
                break;
            }
            $writer->write($uid = $this->atomic->add());
            $writer->flush();
            $this->engine->submit($uid, $data);
        }
    }


    /**
     * @param BuffIO $writer
     * @param BuffIO $reader
     * @throws JsonException
     */
    public function subscribe(BuffIO $writer, BuffIO $reader): void
    {
        for (;;) {
            if (! $reader->isLive() ) {
                break;
            }
            if ( $result = $this->engine->pullOneResult() ) {
                $msg = json_encode($result, JSON_THROW_ON_ERROR);
                $writer->write(pack('N', strlen($msg)).$msg);
                $writer->flush();
            } else {
                // @fixme(wilon) If no result to send, just sleep(1) to yield
                Coroutine::sleep(1);
            }
        }
    }
}