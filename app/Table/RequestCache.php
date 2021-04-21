<?php


namespace App\Table;


use Swoole\Table;

class RequestCache extends CacheAbstract
{

    /**
     * @return string
     */
    public function getMetaFile(): string
    {
        return RUNTIME_PATH.'/request.dat';
    }

    /**
     * @param int $size
     * @return Table
     */
    public function getTable(int $size): Table
    {
        $table = new Table($size);
        $table->column('id', Table::TYPE_INT);
        $table->column('data', Table::TYPE_STRING, 1024);
        $table->create();
        return  $table;
    }

}