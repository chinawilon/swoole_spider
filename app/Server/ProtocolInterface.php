<?php


namespace App\Server;


use App\Engine\EngineInterface;

interface ProtocolInterface
{
    public function handle(FdInterface $fd, EngineInterface $engine): void ;
}