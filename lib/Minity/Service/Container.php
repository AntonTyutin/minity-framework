<?php

namespace Minity\Service;

use Minity\Util\MultilevelBag;

/**
 * Контейнер внедренных зависимостей
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class Container
{

    /**
     * Конфигурация контейнера
     * @var array
     */
    private $config = array();

    /**
     * Хранилище инстанцированных сервисов
     * @var array
     */
    private $services = array();

    /**
     * Параметры для сервисов
     * @var \Minity\Util\MultilevelBag
     */
    private $params;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->services = array();
        $this->config   = array();
        $this->set('services', $this);
    }

    /**
     * @param string $name  Имя сервиса
     * @return object  Объект-сервис
     * @throws \RuntimeException Если сервис не найден в контейнере
     */
    public function get($name)
    {
        if (!isset($this->config[$name])) {
            throw new \RuntimeException(
                sprintf('Запрошенный сервис "%s" не найден в контейнере', $name)
            );
        }
        if (!isset($this->services[$name])) {
            $this->services[$name] =
                $this->loadService($name, $this->config[$name]);
        }
        return $this->services[$name];
    }

    public function set($name, $instance)
    {
        $this->config[$name]   = 'direct_set';
        $this->services[$name] = $instance;
    }

    public function has($name)
    {
        return isset($this->config[$name]);
    }

    public function load(array $configuration)
    {
        $this->init();
        $this->config = array_replace($this->config, $configuration);
    }

    protected function loadService($serviceName, $serviceConf)
    {
        static $inProcess = array();
        if (isset($inProcess[$serviceName])) {
            throw new \RuntimeException(
                'Обнаружена циклическая зависимость в контейнере сервисов'
            );
        }
        $inProcess[$serviceName] = true;
        $serviceClass = new \ReflectionClass($serviceConf['class']);
        $args = @$serviceConf['args'] ? : array();
        foreach ($args as $idx => $arg) {
            if ($arg[0] == '@') {
                $dependencyName = substr($arg, 1);
                $arg = $this->has($dependencyName)
                    ? $this->get($dependencyName)
                    : $this->params->get($dependencyName, '%undef%');
                if ('%undef%' === $arg) {
                    throw new \RuntimeException(
                        sprintf(
                            'Зависимость "%s" не найдена в конфигурации',
                            $dependencyName
                        )
                    );
                }
                $args[$idx] = $arg;
            }
        }
        $service = $serviceClass->newInstanceArgs($args);
        unset($inProcess[$serviceName]);
        return $service;
    }

    public function setParams(MultilevelBag $params)
    {
        $this->params = $params;
    }
}
