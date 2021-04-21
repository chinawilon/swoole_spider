<?php


namespace App\Table;


use Swoole\Lock;
use Swoole\Table;

abstract class CacheAbstract implements CacheInterface
{

    /**
     * @var Table
     */
    protected $table;

    /**
     * @var Lock
     */
    protected $mutex;

    /**
     * CacheAbstract constructor.
     *
     * @param $size
     */
    public function __construct($size)
    {
        $this->table = $this->getTable($size);
        $this->mutex = new Lock();
        $this->load(); // load the metadata
    }

    /**
     * @param int $size
     * @return Table
     */
    abstract public function getTable(int $size): Table ;

    /**
     * @return string
     */
    abstract public function getMetaFile(): string ;

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

    /**
     * Sync the CacheAbstract to metadata file
     */
    public function sync(): void
    {
        $meta = [];
        foreach( $this->table as $row ) {
            $meta[$row['id']] = $row;
        }
        file_put_contents($this->getMetaFile(), serialize($meta));
    }

    /**
     * Load the metadata file to CacheAbstract
     */
    public function load(): void
    {
        if (! file_exists($this->getMetaFile()) ) {
            // try to create it
            file_put_contents($this->getMetaFile(), '');
            return ;
        }

        $meta = file_get_contents($this->getMetaFile());
        if ( $meta === '' ) {
            return;
        }
        $data = unserialize($meta, ['allowed_classes'=>true]);
        foreach($data as $row) {
            $this->table->set($row['id'], $row);
        }
    }
}