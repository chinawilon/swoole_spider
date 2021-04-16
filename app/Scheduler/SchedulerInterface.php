<?php

namespace App\Scheduler;

use App\Server\Request;
use Swoole\Coroutine\Channel;

interface SchedulerInterface
{
    public function submit(Request $request);
    public function workerChan(): Channel;
    public function workerReady(Channel $channel);
    public function run();
    public function shutdown();
}