<?php

namespace Minity\Controller;

use Minity\Service\Container;
use Minity\Http\Request;
use Minity\Http\Response;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
abstract class AbstractController
{
    /**
     * Контейнер сервисов
     * @var \Minity\Service\Container
     */
    private $services;

    /**
     * Запрос
     * @var \Minity\Http\Request
     */
    private $request;

    /**
     * Имя метода контроллера, выполняемого командой execute
     * @var string
     */
    private $actionMethodName;

    function __construct(Container $services, Request $request)
    {
        $this->services = $services;
        $this->request  = $request;
    }

    /**
     * @return \Minity\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Minity\Service\Container
     */
    public function getServices()
    {
        return $this->services;
    }

    protected function render($template, $data, $headers = array())
    {
        $view = $this->services->get('view');
        return new Response($view->render($template, $data), 200, $headers);
    }

    protected function redirect($url = '')
    {
        if ($url === '') {
            $url = $this->getRequest()->getServer()->get('REQUEST_URI');
        }
        return new Response('', 302, array('Location' => $url));
    }

    /**
     * @return \Minity\Http\Response
     * @throws \RuntimeException
     */
    final public function execute()
    {
        $response = $this->{$this->actionMethodName}();
        if (!$response instanceof Response) {
            throw new \RuntimeException(
                'Контроллер должен вернуть объект ответа'
            );
        }
        return $response;
    }

    final public function setExecutableAction($methodName)
    {
        if (!method_exists($this, $methodName)) {
            throw new \RuntimeException(
                sprintf(
                    'Метод контроллера "%s:%s" не существует',
                    get_class($this),
                    $methodName
                )
            );
        }
        $this->actionMethodName = $methodName;
    }
}
