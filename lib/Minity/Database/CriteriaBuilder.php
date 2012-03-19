<?php

namespace Minity\Database;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class CriteriaBuilder
{

    const OPERATION_EQUALS            = '=';
    const OPERATION_NOT_EQUALS        = '<>';
    const OPERATION_GREATER_THEN      = '>';
    const OPERATION_LESS_THEN         = '<';
    const OPERATION_GREATER_OR_EQUALS = '>=';
    const OPERATION_LESS_OR_EQUALS    = '<=';
    const OPERATION_IN                = 'in';


    /**
     * Массив параметров фильтрации
     * array(
     *     // альтернативные условия (соединяются по OR)
     *     array(
     *         // совместные условия (соединяются по AND)
     *         array(
     *             'field'     => имя поля,
     *             'operation' => операция сравнения,
     *             'value'     => значение для сравнения
     *         ),
     *         ... // другие совместные условия
     *     ),
     *     ... // другие альтернативные условия
     * )
     * @var array
     */
    protected $filter = array();

    protected $alternative = array();

    protected $order = array();

    protected $limit = 0;

    protected $offset = 0;

    private $curFilterField;

    private $curOrderField;

    private function newAlternative()
    {
        $this->filter[]    = $this->alternative;
        $this->alternative = array();
    }

    private function makeFieldArray($operation, $value)
    {
        return array(
            'field'     => $this->curFilterField,
            'operation' => $operation,
            'value'     => $value
        );
    }

    // filter

    public function field($fieldName)
    {
        return $this->andField($fieldName);
    }

    public function andField($fieldName)
    {
        $this->curFilterField = $fieldName;
        return $this;
    }

    public function orField($fieldName)
    {
        $this->newAlternative();
        $this->curFilterField = $fieldName;
        return $this;
    }

    public function equals($value)
    {
        $this->alternative[] =
            $this->makeFieldArray(self::OPERATION_EQUALS, $value);
        return $this;
    }

    public function lessThen($value)
    {
        $this->alternative[] =
            $this->makeFieldArray(self::OPERATION_LESS_THEN, $value);
        return $this;
    }

    public function lessThenOrEquals($value)
    {
        $this->alternative[] =
            $this->makeFieldArray(self::OPERATION_LESS_OR_EQUALS, $value);
        return $this;
    }

    public function greaterThen($value)
    {
        $this->alternative[] =
            $this->makeFieldArray(self::OPERATION_GREATER_THEN, $value);
        return $this;
    }

    public function greaterThenOrEquals($value)
    {
        $this->alternative[] =
            $this->makeFieldArray(self::OPERATION_GREATER_OR_EQUALS, $value);
        return $this;
    }

    public function notEquals($value)
    {
        $this->alternative[] =
            $this->makeFieldArray(self::OPERATION_NOT_EQUALS, $value);
        return $this;
    }

    public function in(array $list)
    {
        $this->alternative[] =
            $this->makeFieldArray(self::OPERATION_IN, $list);
        return $this;
    }

    // order

    public function orderBy($field)
    {

        return $this;
    }

    public function asc()
    {

        return $this;
    }

    public function desc()
    {

        return $this;
    }

    // limit

    public function limit($num)
    {

        return $this;
    }

    public function offset($num)
    {

        return $this;
    }

    public function build()
    {
        $this->newAlternative();
        return array(
            'filter' => $this->filter,
            'order'  => $this->order,
            'limit'  => $this->limit,
            'offset' => $this->offset
        );
    }

    public static function create()
    {
        return new self();
    }

}
