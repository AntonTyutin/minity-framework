<?php

namespace Minity\Http;

use Minity\Util\MultilevelBag;

/**
 * Ответ приложения
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class Response
{
    /**
     * Заголовки ответа
     * @var array
     */
    protected $headers;

    protected $statusCode;

    protected $content;

    function __construct($content, $statusCode = 200, array $headers = array())
    {
        $this->headers = array_replace(
            array(
                'Content-Type' => 'text/html; charset=UTF-8',
            ),
            $headers
        );
        $this->content    = $content;
        $this->statusCode = $statusCode;
    }

    public function getHeader($name, $default = null)
    {
        return isset($this->headers[$name])
            ? $this->headers[$name]
            : $default;
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function send()
    {
        foreach ($this->headers as $headerName => $headerValue) {
            header(
                sprintf('%s: %s', $headerName, $headerValue),
                true,
                $this->statusCode
            );
        }
        echo $this->content;
    }
}
