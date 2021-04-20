<?php


namespace App\Table;


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
     * @var string
     */
    private $metadata = RUNTIME_PATH.'/metadata.dat';

    /**
     * Cache constructor.
     *
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->mutex = new Lock();
        $this->load(); // load the metadata
    }

    /**
     * @param string $key
     * @param array $data
     */
    public function put(string $key, array $data): void
    {
        $this->table->set($key, $data);
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
     * Sync the Cache to metadata file
     */
    public function sync(): void
    {
        $meta = [];
        foreach( $this->table as $row ) {
            $meta[$row['id']] = $row;
        }
        file_put_contents($this->metadata, serialize($meta));
    }

    /**
     * Load the metadata file to Cache
     */
    public function load(): void
    {
        $meta = file_get_contents($this->metadata);
        if ( $meta === '' ) {
            return;
        }
        $data = unserialize($meta, ['allowed_classes'=>true]);
        foreach($data as $row) {
            $this->table->set($row['id'], $row);
        }
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
        if ( $data === null ) {
            // When the loop return the null data,
            // Rewind the loop pointer
            $this->table->rewind();
            $this->mutex->unlock();
            return false;
        }
        $this->mutex->unlock();
        $this->table->delete($key);
        return $data;
    }

}