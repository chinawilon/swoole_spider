<?php


namespace App\Engine;


use App\Server\Request;

interface EngineInterface
{
    public function submit(Request $request): void ;
    public function shutdown(): void ;
    public function run(): void ;
    public function pullOneResult();
    public function pullResult(string $key);
    public function putResult(string $key, array $data);
}