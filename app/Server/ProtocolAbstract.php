<?php


namespace App\Server;


use App\Engine\EngineInterface;
use Swoole\Atomic;

class ProtocolAbstract
{
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
     * Spider Protocol type
     */
    public const TYPE_SUB = 'SUB ';
    public const TYPE_PUB = 'PUB ';

    /**
     * ProtocolAbstract constructor.
     *
     * @param EngineInterface $engine
     */
    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
        $this->setAtomic();
    }

    public function setAtomic(): void
    {
        $this->atomic = new Atomic(time());
    }
}