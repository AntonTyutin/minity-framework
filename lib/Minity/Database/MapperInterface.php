<?php

namespace Minity\Database;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
interface MapperInterface
{

    /**
     * Получение объекта из БД по его идентификатору
     * @abstract
     * @param integer $id
     * @return object
     */
    function getById($id);

    /**
     * Получение списка объектов из хранилища по критериям
     * @abstract
     * @param array $criteria
     * @return Collection
     */
    function find($criteria);

    /**
     * Получение первого объекта из хранилища по критериям
     * @abstract
     * @param array $criteria
     * @return object
     */
    function findOne($criteria);

    /**
     * Добавление объекта в хранилище
     * @abstract
     * @param object $object
     * @return boolean Успешность выполнения операции
     */
    function insert($object);

    /**
     * Обновление объекта в хранилище
     * @abstract
     * @param object $object
     * @return boolean Успешность выполнения операции
     */
    function update($object);

    /**
     * Удаление объекта их хранилища
     * @abstract
     * @param object $object
     * @return boolean Успешность выполнения операции
     */
    function delete($object);

    function install();
}
