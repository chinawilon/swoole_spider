<?php


namespace App\Server;


interface FdInterface
{
    public function write(string $msg): void ;
    public function read(): string ;
}