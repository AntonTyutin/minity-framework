<?php

namespace Minity\Http;

use Minity\Util\Bag;

/**
 * Запрос к приложению
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class Request
{

    const METHOD_POST = 'post';
    const METHOD_GET  = 'get';
    const METHOD_HEAD = 'head';
    const METHOD_PUT  = 'put';

    /**
     * Переменные сервера (копия $_SERVER)
     * @var \Minity\Util\Bag
     */
    protected $server;

    /**
     * Параметры запроса (копия $_REQUEST)
     * @var \Minity\Util\Bag
     */
    protected $parameters;

    /**
     * Параметры сессии пользователя (копия $_SESSION)
     * @var \Minity\Util\Bag
     */
    protected $session;

    public static function createFromGlobals()
    {
        return new static($_SERVER, $_REQUEST, $_SESSION);
    }

    function __construct(array $server, array $parameters, array $session)
    {
        $this->server     = new Bag($server);
        $this->parameters = new Bag($parameters);
        $this->session    = new Bag($session);
    }


    /**
     * Возвращает запрошенный URI
     * @return string
     */
    function getUri()
    {
        return $this->server->get('REQUEST_URI');
    }

    /**
     * Возвращает тип запроса
     * @return string
     */
    function getMethod()
    {
        return strtolower($this->server->get('REQUEST_METHOD'));
    }

    public function isAjax()
    {
        return strtolower($this->server->get('HTTP_X_REQUESTED_WITH'))
                        == 'xmlhttprequest';
    }

    public function get($param, $default = null)
    {
        return $this->parameters->get($param, $default);
    }

    public function set($param, $value)
    {
        $this->parameters->set($param, $value);
    }

    public function has($param)
    {
        return $this->parameters->has($param);
    }

    /**
     * @return \Minity\Util\Bag
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return \Minity\Util\Bag
     */
    public function getServer()
    {
        return $this->server;
    }
}
