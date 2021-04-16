<?php


namespace App\Server;


use App\Exceptions\SpiderException;

class BuffIO
{
    /**
     * @var string
     */
    private $left = "";

    /**
     * Read the connection by `delimiter`
     */
    public const READ_STR_MODE = 0;

    /**
     * Read the connection by specify `nums`
     */
    public const READ_NUM_MODE = 1;

    /**
     * @var FdInterface
     */
    private $fd;


    public function __construct(FdInterface $fd)
    {
        $this->fd = $fd;
    }

    /**
     * @param string $send
     */
    public function write(string $send): void
    {
        $this->left .= $send;
    }

    /**
     * flush the buffer
     */
    public function flush(): void
    {
        $this->fd->write($this->left);
    }

    /**
     * @param $what int|string
     * @param $mode
     * @return false|string
     * @throws SpiderException
     */
    public function read($what, $mode = self::READ_NUM_MODE)
    {
        while (true) {
            switch ($mode) {
                case self::READ_NUM_MODE:
                    if ( strlen($this->left) < $what ) {
                        $this->left .= $this->fd->read();
                        continue 2;
                    }
                    $ret = substr($this->left, 0, $what);
                    $this->left = substr($this->left, $what);
                    return $ret;
                case self::READ_STR_MODE:
                    $pos = strpos($this->left, $what);
                    if ( false === $pos ) {
                        $this->left .= $this->fd->read();
                        continue 2;
                    }
                    $ret = substr($this->left, 0, $pos);
                    $this->left = substr($this->left, $pos+strlen($what));
                    return $ret;
                default:
                    throw new SpiderException("Read mode error");
            }
        }
    }
}