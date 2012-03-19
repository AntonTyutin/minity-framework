<?php

namespace Minity\Controller;

use Minity\Service\Container;
use Minity\Http\Request;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class ActionFactory
{
    private $services;

    private $request;

    public function __construct(Container $services, Request $request)
    {
        $this->services = $services;
        $this->request  = $request;
    }


    public static function getControllerClass($name)
    {
        return $name . 'Controller';
    }

    public static function getActionMethod($name)
    {
        return 'action' . ucfirst($name);
    }

    /**
     * @param string $controller   Имя контроллера
     * @param string $action       Имя действия
     * @return \Minity\Controller\AbstractController
     */
    function create($controller, $action)
    {
        $controllerClass = self::getControllerClass($controller);
        $actionMethod = self::getActionMethod($action);
        self::validate($controllerClass, $actionMethod);
        $controller = new $controllerClass(
            $this->services,
            $this->request
        );
        $controller->setExecutableAction($actionMethod);
        return $controller;
    }

    private static function validate($class, $method) {
        if ($class && !class_exists($class)) {
            throw new \RuntimeException(
                sprintf(
                    'Класс контроллера "%s" не найден',
                    $class
                )
            );
        }
        if ($class
                && $method
                && !method_exists($class, $method)
        ) {
            throw new \RuntimeException(
                sprintf(
                    'Метод контроллера "%s:%s" не существует',
                    $class,
                    $method
                )
            );
        }
    }
}
