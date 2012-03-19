<?php

namespace Minity\Application;

use Minity\Http\Request;
use Minity\Service\Container;
use Minity\Util\MultilevelBag;
use Minity\Http\NotFoundException;
use Minity\Http\DeniedException;
use Minity\Controller\ErrorController;

/**
 * Класс приложения
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class Application
{
    /**
     * Конфигурация приложения
     * @var \Minity\Util\MultilevelBag
     */
    private $configuration;

    /**
     * @var \Minity\Service\Container
     */
    private $services;

    /**
     * @param \Minity\Util\MultilevelBag $configuration
     */
    function __construct(MultilevelBag $configuration)
    {
        $this->configuration = $configuration;
        $this->configuration->set(
            'router.routes.error404',
            array(
                'action' => 'Minity\Controller\Error:notFound'
            )
        );
        $this->configuration->set(
            'router.routes.error403',
            array(
                'action' => 'Minity\Controller\Error:denied'
            )
        );
        $this->configuration->set(
            'router.routes.error500',
            array(
                'action' => 'Minity\Controller\Error:crash'
            )
        );
        $this->services = new Container();
        $this->services->setParams($configuration);
        $this->services->load($configuration->get('services', array()));

        $services = $this->services;
        $isDebugMode = $this->configuration->get('errors.display', false);
        set_exception_handler(
            function ($exception) use ($services, $isDebugMode)
            {
                $request = new Request(array(), array(), array());
                $services->set('request', $request);
                if ($isDebugMode) {
                    $request->set('exception', $exception);
                }
                $controller = new ErrorController($services, $request);
                $controller->setExecutableAction('actionCrash');
                $controller->execute()->send();
            }
        );
        set_error_handler(
            function ($errno, $errstr, $errfile, $errline ) {
                if (error_reporting() & $errno) {
                    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
                }
            }
        );

        ini_set(
            'display_errors',
            $this->configuration->get('errors.display', false) ? 'On' : 'Off'
        );
        error_reporting($this->configuration->get('error.level', E_ALL));
    }

    /**
     * Выполняет действие в соответствии с запросом.
     * Действие выбирается на основе правил раутинга.
     * @param \Minity\Http\Request $request
     * @return \Minity\Http\Response
     */
    public function handle(Request $request)
    {
        /* @var $router \Minity\Controller\Router */
        $router = $this->services->get('router');
        try {
            $routeResult = $router->match($request->getUri());
        } catch (NotFoundException $e) {
            $routeResult = $router->createResult('error404');
        } catch (DeniedException $e) {
            $routeResult = $router->createResult('error403');
        }
        return $this->execute(
            $routeResult['controller'] . ':' . $routeResult['action'],
            $request
        );
    }


    /**
     * Выполняет конкретное действие без использования раутинга
     * @param string $action
     * @param \Minity\Http\Request $request
     * @return \Minity\Http\Response
     */
    public function execute($action, Request $request)
    {
        $action = explode(':', $action, 2);
        $controller = $action[0];
        $action = isset($action[1]) ? $action[1] : 'index';
        $this->services->set('request', $request);
        $controller = $this->services->get('action_factory')
            ->create($controller, $action);
        return $controller->execute();
    }

    /**
     * @return \Minity\Service\Container
     */
    public function getServices()
    {
        return $this->services;
    }
}
