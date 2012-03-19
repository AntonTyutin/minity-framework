<?php

namespace Minity\Database;

use Minity\Database\DriverInterface;

/**
 * Пул мэпперов
 *
 * Позволяет получить мэппер для определенного класса модели
 *
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class MapperPool
{
    /**
     * Соотствие имени класса модели имени класса мэппера
     * @var array
     */
    private $map = array();

    /**
     * Инстанцированные мэпперы
     * @var array
     */
    private $mappers = array();

    /**
     * @var \Minity\Database\DriverInterface
     */
    private $driver;

    public function __construct(DriverInterface $driver, array $map)
    {
        $this->driver = $driver;

        foreach ($map as $modelClass => $mapperClass) {
            if (!class_exists($modelClass)) {
                throw new \RuntimeException(
                    sprintf(
                        'Указанный класс модели "%s" не существует',
                        $modelClass
                    )
                );
            }
            if (!class_exists($mapperClass)) {
                throw new \RuntimeException(
                    sprintf(
                        'Указанный класс мэппера данных "%s" не существует',
                        $mapperClass
                    )
                );
            }
            $classRef = new \ReflectionClass($mapperClass);
            if (!$classRef->isSubclassOf('Minity\Database\AbstractMapper')) {
                throw new \RuntimeException(
                    sprintf(
                        'Указанный класс "%s" не является классом мэппера',
                        $mapperClass
                    )
                );
            }
            $this->map[$modelClass] = $mapperClass;
        }
    }

    /**
     * Получение объекта мэппера по имени класса модели
     * @param string $modelClass
     * @return \Minity\Database\MapperInterface
     * @throws \RuntimeException Если не найден соответствующий мэппер
     */
    public function get($modelClass)
    {
        if (!isset($this->map[$modelClass])) {
            throw new \RuntimeException(
                sprintf(
                    'Мэппер для модели "%s" не зарегистрирован в пулле',
                    $modelClass
                )
            );
        }
        if (!isset($this->mappers[$modelClass])) {
            $mapperClass = $this->map[$modelClass];
            $this->mappers[$modelClass] =
                new $mapperClass($this->driver, $this);
        }
        return $this->mappers[$modelClass];
    }
}
