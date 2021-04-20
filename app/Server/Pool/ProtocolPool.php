<?php


namespace App\Server\Pool;

use App\Server\ProtocolAbstract;
use App\Server\Request;
use Co\Server\Connection;
use JsonException;

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
     * @throws JsonException
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
            $request = json_decode($data, true, 512, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            $writer->write($uid = $this->atomic->add());
            $writer->flush();
            $this->engine->submit(new Request($uid, $request));
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
            }
        }
    }
}