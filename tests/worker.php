<?php

use Swoole\Coroutine\Channel;
use Swoole\Coroutine\WaitGroup;
use Swoole\Server;

$chan = new Channel(100);
$wg = new WaitGroup();
$running = true;

$host = '0.0.0.0';
$port = 9111;
$server = new Server($host, $port);
$server->set([
    'task_enable_coroutine' => true,
    'reload_async' => true,
    'hook_flags' => SWOOLE_HOOK_ALL
]);

$helper = new Helper();

$server->on('receive', function (Server $server, $fd, $reactorId, string $data) use($helper){
    $server->send($fd, 'response => '.$data);
    $helper->push($data);
});

$server->on('WorkerExit', function (Server $server, int $workerId) use($helper) {
    echo 'WorkerExit';
    $helper->shutdown();
});

$server->on('WorkerStart', function(Server $server, $workerId) use($helper, &$running){
    $chan = new Channel(1000);
    $helper->setChanel($chan);
    go(static function () use($chan, $helper) {
        while ($helper->isRunning) {
            if (! $data = $chan->pop() ) {
                break;
            }
            go(static function() use($data){
                sleep(10); // worker exit timeout, forced termination
                echo $data.PHP_EOL;
            });
        }
    });
});

echo sprintf("%s...(%s:%s)\n", 'start', $host, $port);
$server->start();


class Helper
{
    /**
     * @var Channel
     */
    private $chan;
    /**
     * @var bool
     */
    public $isRunning = true;

    /**
     * @param string $data
     */
    public function push(string $data): void
    {
        if ( $this->chan ) {
            $this->chan->push(trim($data));
        }
    }

    /**
     * @param Channel $channel
     */
    public function setChanel(Channel $channel): void
    {
        $this->chan = $channel;
    }
    /**
     * Shutdown.
     * Wait all coroutine end.
     */
    public function shutdown(): void
    {
        $this->isRunning = false;
        if ( $this->chan ) {
            $this->chan->close();
        }
    }

}