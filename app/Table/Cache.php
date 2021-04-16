<?php

namespace App\Table;

use Swoole\Table;

class Cache implements CacheInterface
{
    /**
     * @var Table
     */
    private $table;

    private $cache = [];

    /**
     * Cache constructor.
     *
     * @param $size
     */
    public function __construct($size)
    {
        $this->table = new Table($size);
        $this->table->column('id', Table::TYPE_INT);
        $this->table->column('status_code', Table::TYPE_INT);
        $this->table->column('response_body', Table::TYPE_STRING, 1024);
        $this->table->create();
    }

    /**
     * Put the result
     *
     * @param string $key
     * @param array $data
     */
    public function put(string $key, array $data): void
    {
        isset($this->cache[$key]) ? $this->table->del($key) : $this->cache[$key] = $key;
        $this->table->set($key, $data);
    }

    /**
     * Pull the result and delete it
     *
     * @param string $key
     * @return mixed|null
     */
    public function pull(string $key)
    {
        if ( isset($this->cache[$key] )) {
            $result = $this->table->get($key);
            unset($this->cache[$key]);
            $this->table->del($key);
            return $result;
        }
        return false;
    }

    /**
     * Shift the first Node
     *
     * @return mixed
     */
    public function shift()
    {
        if (empty($this->cache)) {
            return false;
        }
        $key = array_shift($this->cache);
        $result = $this->table->get($key);
        $this->table->del($key);
        return $result;
    }

    /**
     * Pop the last Node
     *
     * @return mixed
     */
    public function pop()
    {
        if (empty($this->cache)) {
            return false;
        }
        $key = array_pop($this->cache);
        $result = $this->table->get($key);
        $this->table->del($key);
        return $result;
    }

}