<?php

namespace Minity\Database;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
interface DriverInterface
{
    /**
     * Выполняет запрос к базе данных и возвращает успешность его выполнения.
     * @abstract
     * @param string $query
     * @return boolean
     */
    function execute($query);

    /**
     * Выполняет запрос на выборку данных из базы.
     * @abstract
     * @param $query
     * @return Collection Результат выборки
     */
    function find($query);
}
