<?php


use Swoole\Coroutine;
use Swoole\Process\Pool;
use function Swoole\Coroutine\run;

require __DIR__ . '/../vendor/autoload.php';


$pool = new Pool($workerNum ?? swoole_cpu_num(), null, null, true);
$pool->on('workerStart', static function ($pool){

    go(static function (){
        for (;;) {
            echo 'a';
            sleep(1);
            echo 'b';
        }
    });
    go(static function (){
        for (;;) {
            echo 'e';
            sleep(1);
            echo 'f';
        }
    });
});

$pool->start();

/*run(function (){
    go(static function (){
        for (;;) {
            echo 'a';
            sleep(1);
            echo 'b';
        }
    });
    go(static function (){
        for (;;) {
            echo 'e';
            sleep(1);
            echo 'f';
        }
    });
});*/