<?php


use App\Table\Cache;
use PHPUnit\Framework\TestCase;
use Swoole\Table;

class CacheTest extends TestCase
{
    public function testTable(): void
    {
        $table = new Table(64);
        $table->column('id', Table::TYPE_INT);
        $table->create();


        for($i=0;$i<100;$i++) {
            $table->set($i,['id'=>$i]);
        }

        foreach($table as $row) {
            var_dump($row);
        }
    }

    public function testCache(): void
    {
        $table = new Table(1<<6);
        $table->column('id', Table::TYPE_INT);
        $table->create();

        $cache = new Cache($table);
        for($i=0;$i<1000;$i++) {
            $cache->put($i,['id'=>$i]);
        }

//        while ( $result = $cache->shift() ) {
//            var_dump($result);
//        }
    }
}