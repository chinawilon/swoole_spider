<?php


namespace App\Engine;

interface EngineInterface
{
    public function run(): void ;
    public function submit(string $key, string $request): void ;
    public function pullOneResult();
    public function pullResult(string $key);
    public function putResult(string $key, array $data);
    public function workerStop(): void ;
    public function shutdown(): void ;
}