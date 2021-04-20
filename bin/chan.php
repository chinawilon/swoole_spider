<?php


use Swoole\Coroutine\Channel;


require __DIR__.'/../bootstrap/app.php';


$pool = new \Swoole\Process\Pool(2);
$pool->on('WorkerStart', function(){

    $chan = new Channel(1000);

    go(static function () use($chan){
        for (;;) {
            if (! $data = $chan->pop()){
                echo 'break';
                break;
            }
        }
    });

    go(static function () use($chan){
        $chan->close();
    });
});
$pool->start();