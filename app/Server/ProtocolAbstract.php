<?php


namespace App\Server;


use App\Engine\EngineInterface;
use App\Table\CacheInterface;
use Swoole\Atomic;

class ProtocolAbstract
{
    /**
     * @var CacheInterface
     */
    protected $cache;
    /**
     * @var EngineInterface
     */
    protected $engine;
    /**
     * @var Atomic
     */
    protected $atomic;

    /**
     * Type Length
     */
    public const TYPE_LENGTH = 4;

    /**
     * Data Length
     */
    public const DATA_LENGTH = 4;

    /**
     * Empty Result
     */
    public const E_EMPTY = 'empty';

    /**
     * Spider Protocol type
     */
    public const TYPE_SUB = 'SUB ';
    public const TYPE_PUB = 'PUB ';

    /**
     * Ready to Receive
     */
    public const TYPE_RDY = 'RDY';

    /**
     * ProtocolAbstract constructor.
     *
     * @param CacheInterface $cache
     * @param EngineInterface $engine
     */
    public function __construct(CacheInterface $cache, EngineInterface $engine)
    {
        $this->cache = $cache;
        $this->engine = $engine;
        $this->setAtomic();
    }

    public function setAtomic(): void
    {
        $this->atomic = new Atomic(time());
    }
}