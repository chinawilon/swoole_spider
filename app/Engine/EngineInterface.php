<?php


namespace App\Engine;


use App\Server\Request;

interface EngineInterface
{
    public function run(): void ;
    public function submit(Request $request): void ;
    public function pullOneResult();
    public function pullResult(string $key);
    public function putResult(string $key, array $data);
    public function workerExit(): void ;
    public function shutdown(): void ;
}