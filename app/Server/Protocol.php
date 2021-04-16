<?php


namespace App\Server;

use App\Engine\EngineInterface;
use App\Exceptions\SpiderException;
use JsonException;

class Protocol implements ProtocolInterface
{
    /**
     * @param FdInterface $fd
     * @param EngineInterface $engine
     * @throws JsonException
     * @throws SpiderException
     */
    public function handle(FdInterface $fd, EngineInterface $engine): void
    {
        $writer = new BuffIO($fd);
        $reader = new BuffIO($fd);
        $type = $reader->read(3);
        echo $type;
        switch ($type) {
            case 'PUB':
                $this->publish($reader, $writer, $engine);
                break;
            case 'SUB':
                $this->subscribe($writer, $reader, $engine);
                break;
            default:
                $writer->write("403 Forbidden");
                $writer->flush();
        }
    }

    /**
     * @param BuffIO $reader
     * @param BuffIO $writer
     * @param EngineInterface $engine
     * @throws JsonException
     * @throws SpiderException
     */
    public function publish(BuffIO $reader, BuffIO $writer, EngineInterface $engine): void
    {
        while (true) {
            [, $length] = unpack('n', $reader->read(2));
            $data = $reader->read($length);
            $request = json_decode($data, true, 512, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            $engine->submit(new Request($request));
            $writer->write(time());
            $writer->flush();
        }
    }


    /**
     * @param BuffIO $writer
     * @param BuffIO $reader
     * @param EngineInterface $engine
     */
    public function subscribe(BuffIO $writer, BuffIO $reader, EngineInterface $engine): void
    {
        //
    }
}