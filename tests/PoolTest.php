<?php


use PHPUnit\Framework\TestCase;
use Swoole\Process\Pool;
use Swoole\Table;
use function Swoole\Coroutine\run;

class PoolTest extends TestCase
{
    public function testPool(): void
    {
        $pool = new Pool( swoole_cpu_num(), null, null, true);
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
    }

    public function testTable2(): void
    {
        $table = new Table(4096, 0.25);
        $table->column('id', Table::TYPE_INT);
        $table->create();

        $pool = new Pool(2);
        $pool->on('WorkerStart', function () use($table) {
            $table->set(random_int(0, 100), ['id'=>random_int(0, 100)]);
            foreach( $table as $row) {
                    echo '-----'.PHP_EOL;
                    var_dump($row);
                }

        });
        $pool->start();

    }

    public function testCache2(): void
    {
        $table = new Table(4096, 0.25);
        $table->column('id', Table::TYPE_INT);
        $table->create();
        $cache = new \App\Table\Cache($table);

        $pool = new Pool(2);
        $pool->on('WorkerStart', function () use($cache) {
            $cache->put(random_int(0, 100), ['id'=>random_int(0, 100)]);
            var_dump($cache->shift());
//            while( $data = $cache->shift() ) {
//                var_dump($data);
//            }

        });
        $pool->start();

    }


    public function testRun(): void
    {
        run(function (){
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
    }
}