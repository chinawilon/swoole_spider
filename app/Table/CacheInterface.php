<?php


namespace App\Table;


use Swoole\Table;

interface CacheInterface
{
    public function put(string $key, array $data): void ;
    public function get(string $key) ;
    public function pull(string $key) ;
    public function shift() ;
    public function sync() ;
    public function load() ;
    public function getTable(int $size): Table ;
    public function getMetaFile(): string ;
}