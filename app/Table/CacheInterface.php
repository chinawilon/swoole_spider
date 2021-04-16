<?php


namespace App\Table;


interface CacheInterface
{
    public function put(string $key, array $data): void ;
    public function pull(string $key) ;
    public function shift();
    public function pop();
}