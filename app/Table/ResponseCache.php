<?php


namespace App\Table;


use Swoole\Table;

class ResponseCache extends CacheAbstract
{
    /**
     * @return string
     */
    public function getMetaFile(): string
    {
        return RUNTIME_PATH.'/response.dat';
    }

    /**
     * @param int $size
     * @return Table
     */
    public function getTable(int $size): Table
    {
        $table = new Table($size);
        $table->column('id', Table::TYPE_INT);
        $table->column('status_code', Table::TYPE_INT);
        $table->column('response_body', Table::TYPE_STRING, 1024);
        $table->create();
        return $table;
    }

}