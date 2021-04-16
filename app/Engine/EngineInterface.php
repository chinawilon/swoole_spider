<?php


namespace App\Engine;


use App\Server\Request;

interface EngineInterface
{
    public function run(): void ;
    public function submit(Request $request): void ;
    public function shutdown(): void ;
}