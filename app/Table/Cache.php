<?php


namespace App\Table;

use Swoole\Atomic;
use Swoole\Lock;
use Swoole\Table;

class Cache implements CacheInterface
{

    /**
     * @var Table
     */
    private $table;

    /**
     * @var Lock
     */
    private $mutex;
    /**
     * @var Atomic
     */
    private $errNum;

    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->mutex = new Lock();
        $this->errNum = new Atomic();
    }

    /**
     * @return mixed
     */
    public function getErrNum(): int
    {
        return $this->errNum->get();
    }

    /**
     * @param string $key
     * @param array $data
     */
    public function put(string $key, array $data): void
    {
        @$this->table->set($key, $data);
        echo 'put done'.PHP_EOL;
        if (error_get_last()) {
            $this->errNum->add();

            // @fixme how to do? save it??
            // $this->pop();
            // $this->set($key, $data); // death loop?
            // $this->pop();
            file_put_contents(ROOT_PATH . '/runtime/cache.error.log',
                sprintf("[%s] %s => %s\n", date("Y-m-d H:i:s"), $key, serialize($data)),
                FILE_APPEND,
            );

        }
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function pull(string $key)
    {
        $this->mutex->lock();
        if ( $data = $this->get($key) ) {
            $this->table->delete($key);
            $this->mutex->unlock();
            return $data;
        }
        $this->mutex->unlock();
        return false;
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function get(string $key )
    {
        return $this->table->get($key) ?? false;
    }

    /**
     * @return bool|mixed
     */
    public function shift()
    {
        $this->mutex->lock();
        $this->table->next();
        $data = $this->table->current();
        $key = $this->table->key();
        $this->mutex->unlock();
        if ( $data === null ) {
            return false;
        }
        $this->table->delete($key);
        return $data;
    }

}