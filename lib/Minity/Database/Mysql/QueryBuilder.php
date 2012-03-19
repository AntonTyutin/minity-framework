<?php

namespace Minity\Database\Mysql;

use Minity\Database\CriteriaBuilder;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class QueryBuilder
{
    const TYPE_SELECT = 1;
    const TYPE_INSERT = 2;
    const TYPE_UPDATE = 3;
    const TYPE_DELETE = 4;

    private $tableName;

    private $type = self::TYPE_SELECT;

    private $fields = array();

    private $where  = array();

    private $order  = array();

    private $group  = array();

    private $limit;

    private $offset;

    private $record = array();

    public function select()
    {
        $this->type = self::TYPE_SELECT;
        $this->fields = func_get_args();
        return $this;
    }

    public function from($tableName)
    {
        $this->setTable($tableName);
        return $this; 
    }

    public function where(array $criteria)
    {
        $this->where = $criteria;
        return $this;
    }

    public function order(array $criteria)
    {
        $this->order = $criteria;
        return $this;
    }

    public function group()
    {
        $this->group = func_get_args();
        return $this;
    }

    public function limit($num)
    {
        $this->limit = (int)$num;
        return $this;
    }

    public function offset($num)
    {
        $this->offset = (int)$num;
        return $this;
    }

    public function insert($record)
    {
        $this->type = self::TYPE_INSERT;
        return $this->set($record);
    }

    public function into($tableName)
    {
        $this->setTable($tableName);
        return $this; 
    }

    public function in($tableName)
    {
        return $this->into($tableName);
    }

    public function update(array $record)
    {
        $this->type = self::TYPE_UPDATE;
        return $this->set($record);
    }

    public function set(array $record)
    {
        $this->record = $record;
        return $this;
    }

    public function delete($tableName)
    {
        $this->type = self::TYPE_DELETE;
        $this->setTable($tableName);
        return $this; 
    }


    public static function formatName($fieldName)
    {
        return '`'
            . str_replace('`', '\`', mysql_real_escape_string($fieldName))
            . '`';
    }

    public static function formatField($fieldName, $aliased = false)
    {
        if ('@' == $fieldName[0]) { // это выражение
            return self::formatExpression(substr($fieldName, 1), $aliased);
        }
        return '`'
            . str_replace('`', '\`', mysql_real_escape_string($fieldName))
            . '`';
    }

    public static function formatExpression($expression, $aliased = false)
    {
        static $knownExprRegexp = '/(\w+)\s*\((\s*\w+\s*(?:,\s*\w+\s*)*)\)/';
        static $knownFunctions = array('count', 'sum', 'min', 'max');
        if (!preg_match($knownExprRegexp . '', $expression, $matches)) {
            throw new \RuntimeException(
                sprintf('Неизвестное выражение "%s"', $expression)
            );
        }
        $function = $matches[1];
        if (!in_array($function, $knownFunctions)) {
            throw new \RuntimeException(
                sprintf('Неизвестная функция "%s"', $function)
            );
        }
        $args     = array_map('trim', explode(',', $matches[2]));
        foreach ($args as $idx => $arg) {
            $args[$idx] = self::formatField($arg);
        }
        return $function . '(' . implode(', ', $args) .')'
            . ($aliased ? ' ' . $function: '');
    }

    public static function formatValue($value)
    {
        if (gettype($value) == 'array') {
            return '('
                . implode(
                    ', ',
                    array_map(array(__CLASS__, 'formatScalar'), $value)
                )
                . ')';
        }
        return self::formatScalar($value);
    }

    public static function formatScalar($value)
    {
        switch (gettype($value)) {
            case 'NULL':
                return 'null';
            case 'integer':
            case 'double':
                return $value;
            case 'string':
            case 'object':
                return '"' . mysql_real_escape_string((string)$value) . '"';
        }
        throw new \RuntimeException(
            sprintf(
                'Невозможно отформатировать значение типа "%s"',
                gettype($value)
            )
        );
    }


    public function build()
    {
        switch($this->type) {
            case self::TYPE_SELECT: return $this->buildSelect();
            case self::TYPE_UPDATE: return $this->buildUpdate();
            case self::TYPE_INSERT: return $this->buildInsert();
            case self::TYPE_DELETE: return $this->buildDelete();
        }
        throw new \RuntimeException(
            'Неизвестный тип запроса в построителе запросов'
        );
    }

    private function buildSelect()
    {
        return 'select ' . $this->buildFieldList()
            . $this->buildFrom()
            . $this->buildWhere()
            . $this->buildGroup()
            . $this->buildOrder()
            . $this->buildLimit();
    }

    private function buildUpdate()
    {
        return 'update ' . $this->buildTables()
            . $this->buildSet()
            . $this->buildWhere()
            . $this->buildOrder()
            . $this->buildLimit();
    }

    private function buildInsert()
    {
        return 'insert into ' . $this->buildTables()
            . $this->buildSet();
    }

    private function buildDelete()
    {
        return 'delete from ' . $this->buildTables()
            . $this->buildWhere()
            . $this->buildOrder()
            . $this->buildLimit();
    }

    private function buildFieldList()
    {
        $fieldNames = array();
        foreach ($this->fields as $fieldName) {
            $fieldNames[] = $fieldName !== '*'
                ? self::formatField($fieldName, true)
                : '*';
        }
        return implode(', ', $fieldNames);
    }

    private function buildFrom()
    {
        $from = ' from ' . $this->buildTables();
        return $from;
    }

    private function buildTables()
    {
        if (!$this->tableName) {
            throw new \RuntimeException(
                'Не указано имя таблицы для построения запроса'
            );
        }
        return self::formatName($this->tableName);
    }

    private function buildWhere()
    {
        $where = '';
        if ($this->where) {
            $where = ' where ' . $this->buildOrs($this->where);
        }
        return $where;
    }

    private function buildOrs(array $alternatives)
    {
        $ors = array();
        foreach ($alternatives as $alternative) {
            $ors[] = $this->buildAnd($alternative);
        }
        return implode(' or ', $ors);
    }

    private function buildAnd(array $conditions)
    {
        $ands = array();
        foreach ($conditions as $condition) {
            $ands[] = $this->buildCondition($condition);
        }
        return implode(' and ', $ands);

    }

    private function buildCondition(array $condition)
    {
        switch ($condition['operation']) {
            case CriteriaBuilder::OPERATION_EQUALS:
                $conditionString = self::formatName($condition['field'])
                    . ' = ' . self::formatValue($condition['value']);
                break;
            case CriteriaBuilder::OPERATION_NOT_EQUALS:
                $conditionString = self::formatName($condition['field'])
                    . ' <> ' . self::formatValue($condition['value']);
                break;
            case CriteriaBuilder::OPERATION_IN:
                $conditionString = self::formatName($condition['field'])
                    . ' in' . self::formatValue($condition['value']);
                break;
            default:
                throw new \RuntimeException(
                    sprintf(
                        'Неизвестная операция сравнения "%s"',
                        $condition['operation']
                    )
                );
        }
        return $conditionString;
    }

    private function buildLimit()
    {
        if ($this->limit <= 0 && $this->offset > 0) {
            throw new \RuntimeException(
                'Невозможно установить offset без установки limit'
            );
        }
        $limits = '';
        if ($this->limit > 0) {
            $limits = ' limit ' . $this->limit;
            if ($this->offset > 0) {
                $limits .= ' offset ' . $this->offset;
            }
        }
        return $limits;
    }

    private function buildOrder()
    {
        $criterias = array();
        foreach ($this->order as $fieldName => $dir) {
            $direction = strtolower($dir);
            if ($direction !== 'asc' && $direction !== 'desc') {
                throw new \RuntimeException(
                    sprintf(
                        'Неизвестный тип упорядочивания "%s" для поля "%s"',
                        $dir,
                        $fieldName
                    )
                );
            }
            $criterias[] = self::formatField($fieldName) . ' ' . $direction;
        }
        return $criterias ? ' order by ' . implode(', ', $criterias) : '';
    }

    private function buildGroup()
    {
        $criterias = array();
        foreach ($this->group as $fieldName) {
            $criterias[] = self::formatField($fieldName);
        }
        return $criterias ? ' group by ' . implode(', ', $criterias) : '';
    }

    private function buildSet()
    {
        if (!$this->record) {
            throw new \RuntimeException(
                'Не указаны значения полей для обновления'
            );
        }
        $sets = array();
        foreach ($this->record as $fieldName => $value) {
            $sets[] = self::formatField($fieldName)
                . ' = ' . self::formatValue($value);
        }
        return ' set ' . implode(', ', $sets);
    }

    private function setTable($tableName)
    {
        $this->tableName = $tableName;
    }
}
