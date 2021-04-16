<?php


namespace App\Server;


class Request
{

    /**
     * @var array|false|int|string|null
     */
    private $urlInfo;

    /**
     * @var string
     */
    private $url;
    /**
     * @var mixed
     */
    private $method;
    /**
     * @var mixed
     */
    private $header;
    /**
     * @var mixed
     */
    private $body;
    /**
     * @var mixed
     */
    private $timeout;
    private $id;

    public function __construct($id, array $request)
    {
        $this->id = $id;
        $this->url = $request['uri'] ?? '';
        $this->method = $request['method'];
        $this->header = $request['header'];
        $this->body = $request['body'];
        $this->timeout = $request['timeout'];
        $this->urlInfo = parse_url($this->url);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    public function getHost()
    {
        return $this->urlInfo['host'] ?? '';
    }

    public function getPort()
    {
        return $this->urlInfo['port'] ?? 80;
    }

    public function getPath()
    {
        return $this->urlInfo['path'] ?? '';
    }

    public function getQuery()
    {
        return $this->urlInfo['query'] ?? '';
    }

    public function getUrl(): string
    {
        return $this->url;
    }


}