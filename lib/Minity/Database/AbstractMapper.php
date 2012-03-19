<?php

namespace Minity\Database;

/**
 * Абстрактный мэппер
 *
 * @author Anton Tyutin <anton@tyutin.ru>
 */
abstract class AbstractMapper implements MapperInterface
{
    /**
     * @var \Minity\Database\DriverInterface
     */
    private $driver;

    /**
     * @var \Minity\Database\MapperPool
     */
    private $pool;

    public function __construct(DriverInterface $driver, MapperPool $pool) {
        $this->driver = $driver;
        $this->pool   = $pool;
    }

    protected function getIdentityField() {
        return '_id';
    }

    /**
     * Получение объекта из БД по его идентификатору
     * @param integer $id
     *
     * @return object|null
     */
    public function getById($id)
    {
        $criteriaBuilder = new CriteriaBuilder();
        $criteriaBuilder->field($this->getIdentityField())->equals($id);
        return $this->findOne($criteriaBuilder->build());
    }

    /**
     * Получение первого объекта из хранилища по критериям
     * @param array $criteria
     * @return object
     */
    public function findOne($criteria)
    {
        $criteria['limit'] = 1;
        $collection = $this->find($criteria);
        return $collection[0];
    }


    /**
     * Получение объекта модели из ассоциативного массива записи
     * @abstract
     * @param array $record
     * @return object
     */
    abstract public function hydrate(array $record);

    /**
     * Получение ассоциативного массива записи из объекта модели
     * @abstract
     * @param object $object
     * @return array
     */
    abstract public function dehydrate($object);

    /**
     * @return \Minity\Database\DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return \Minity\Database\MapperPool
     */
    public function getMapperPool()
    {
        return $this->pool;
    }
}
