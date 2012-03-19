<?php

namespace Minity\Database\Mysql;

use Minity\Database\AbstractMapper;
use Minity\Database\MapperPool;
use Minity\Database\CriteriaBuilder;
use Minity\Database\DriverInterface;
use Minity\Database\Collection;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
abstract class Mapper extends AbstractMapper
{

    private $tableName;

    private $className;

    private $identityField;

    private $fieldDefs = array();

    /**
     * @var array (имя свойства => имя поля БД)
     */
    private $fieldMap  = array();

    static $typesMap = array(
        'int'      => 'bigint',
        'double'   => 'double',
        'string'   => 'text',
        'text'     => 'mediumtext',
        'datetime' => 'datetime',
    );

    protected abstract function setUp();

    public function __construct(DriverInterface $driver, MapperPool $pool)
    {
        parent::__construct($driver, $pool);
        $def = $this->setUp();
        self::validateTableDef($def);
        $this->tableName = $def['tableName'];
        $this->className = $def['className'];
        foreach ($def['fields'] as $propName => $fieldDef) {
            self::validateFieldDef($propName, $fieldDef, $this->className);
            $this->fieldMap[$propName]  = $fieldDef['name'];
            $this->fieldDefs[$propName] = $fieldDef;
            if (true === @$fieldDef['primary']) {
                if (null !== $this->identityField) {
                    throw new \RuntimeException(
                        'Таблица может иметь только один первичный ключ'
                    );
                }
                $this->identityField = $propName;
            }
        }
    }

    private static function validateFieldDef($propName, $fieldDef, $className)
    {
        $common = 'Ошибка конфигурации мэппинга. ';
        if (!self::isPropValid($propName, $className)) {
            throw new \RuntimeException(
                sprintf(
                    $common . ' Не найдено свойство "%s" в модели "%s".',
                    $propName,
                    $className
                )
            );
        }
        if (!is_array($fieldDef) || !$fieldDef) {
            throw new \RuntimeException(
                sprintf(
                    '%sОпределение мэппинга свойства "%s" не является массивом',
                    $common,
                    $propName
                )
            );
        }
        if (!self::isNameValid(@$fieldDef['name'])) {
            throw new \RuntimeException(
                sprintf(
                    '%sДля свойства "%s" не определено имя поля в таблице БД.',
                    $common,
                    $propName
                )
            );
        }
        if (!self::isFieldTypeValid(@$fieldDef['type'])) {
            throw new \RuntimeException(
                sprintf(
                    '%sУказан неизвестный тип данных в мэппинге свойства "%s".',
                    $common,
                    $propName
                )
            );
        }
    }

    private static function isPropValid($name, $className)
    {
        $classRef = new \ReflectionClass($className);
        return self::isNameValid($name) && $classRef->hasProperty($name);
    }

    private static function isNameValid($name)
    {
        return is_string($name) && trim($name) !== '';
    }

    private static function isFieldTypeValid($type)
    {
        return in_array($type, array_keys(self::$typesMap));
    }

    private static function validateTableDef($def)
    {
        if (empty($def['tableName'])) {
            throw new \RuntimeException(
                'Ошибка конфигурации мэппинга. Не указано имя таблицы.'
            );
        }
        if (empty($def['fields']) || !is_array($def['fields'])) {
            throw new \RuntimeException(
                'Ошибка конфигурации мэппинга. Нет определения полей таблицы.'
            );
        }
    }

    protected function getIdentityField()
    {
        return $this->identityField;
    }


    /**
     * Получение списка объектов из хранилища по критериям
     * @param array $criteria Критерии поиска в виде массива.
     *                        Для построения критериев можно
     *                        использовать Minity\Database\CriteriaBuilder
     *
     * @return \Minity\Database\Collection
     */
    function find($criteria)
    {
        $this->remapFieldNamesInCriteria($criteria);
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->select('*')
            ->from($this->getTableName())
            ->where($criteria['filter'])
            ->order($criteria['order']);
        if ($criteria['limit']) {
            $queryBuilder->limit($criteria['limit']);
        }
        if ($criteria['offset']) {
            $queryBuilder->offset($criteria['offset']);
        }
        $records = $this->getDriver()->find($queryBuilder->build());
        $objects = new Collection();
        foreach ($records as $record) {
            $objects[] = $this->hydrate($record);
        }
        return $objects;
    }

    /**
     * Добавление объекта в хранилище
     * @param object $object
     * @return boolean Успешность выполнения операции
     */
    function insert($object)
    {
        $record = $this->dehydrate($object);
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->insert($record)
            ->into($this->getTableName());

        if (!$this->getDriver()->execute($queryBuilder->build())) {
            return false;
        }
        $lastId = $this->getDriver()->find('select last_insert_id()');
        $idFieldRef = new \ReflectionProperty(get_class($object), 'id');
        $idFieldRef->setAccessible(true);
        $idFieldRef->setValue($object, $lastId[0]['last_insert_id()']);
        return true;
    }

    /**
     * Обновление объекта в хранилище
     * @param object $object
     * @return boolean Успешность выполнения операции
     */
    function update($object)
    {
        $record = $this->dehydrate($object);
        $queryBuilder = $this->createQueryBuilder();
        $id = $record[$this->fieldMap[$this->identityField]];
        $criteria = CriteriaBuilder::create()
            ->field($this->identityField)
            ->equals($id)
            ->build();
        $filter = $criteria['filter'];
        $queryBuilder->update($record)
            ->in($this->getTableName())->where($filter);
        return $this->getDriver()->execute($queryBuilder->build());
    }

    /**
     * Удаление объекта их хранилища
     * @param object $object
     * @return boolean Успешность выполнения операции
     */
    function delete($object)
    {
        $record = $this->dehydrate($object);
        $queryBuilder = $this->createQueryBuilder();
        $id = $record[$this->fieldMap[$this->identityField]];
        $criteria = CriteriaBuilder::create()
            ->field($this->identityField)
            ->equals($id)
            ->build();
        $filter = $criteria['filter'];
        $queryBuilder->delete($this->getTableName())->where($filter);
        return $this->getDriver()->execute($queryBuilder->build());
    }

    protected function createQueryBuilder()
    {
        return new QueryBuilder;
    }

    function install()
    {
        $fields = array();
        foreach ($this->fieldDefs as $def) {
            $fields[] = QueryBuilder::formatName($def['name'])
                . ' ' . self::$typesMap[$def['type']]
                . (isset($def['primary']) && $def['primary'] === true
                    ? ' primary key auto_increment'
                    : ''
                );
        }
        $sql = 'create table ' . QueryBuilder::formatName($this->tableName)
            . '(' . implode(', ', $fields) . ')';
        return $this->getDriver()->execute($sql);
    }

    /**
     * Получение объекта модели из ассоциативного массива записи
     * @param array $record
     * @return object
     */
    public function hydrate(array $record)
    {
        $this->preHydrate($record);
        $className = $this->getClassName();
        $serialized = sprintf('O:%u:"%s":0:{}', strlen($className), $className);
        $object = unserialize($serialized);
        $classRef = $this->getReflectionClass($className);
        foreach ($classRef->getProperties() as $propRef) {
            $fieldName = @$this->fieldMap[$propRef->getName()];
            if ($fieldName && isset($record[$fieldName])) {
                $propRef->setAccessible(true);
                $propRef->setValue($object, $record[$fieldName]);
            }
        }
        $this->postHydrate($object);
        return $object;
    }

    /**
     * Получение ассоциативного массива записи из объекта модели
     * @param object $object
     * @return array
     */
    public function dehydrate($object)
    {
        $this->preUnhydrate($object);
        $className = $this->getClassName();
        if (!$object instanceof $className) {
            throw new \RuntimeException(
                'Попытка преобразования объекта неподходящего класса'
            );
        }
        $record = array();
        $classRef = $this->getReflectionClass($className);
        foreach ($classRef->getProperties() as $propRef) {
            $fieldName = @$this->fieldMap[$propRef->getName()];
            if ($fieldName) {
                $propRef->setAccessible(true);
                $record[$fieldName] = $propRef->getValue($object);
            }
        }
        $this->postUnhydrate($record);
        return $record;
    }

    private function getReflectionClass($className)
    {
        static $cachedReflectionClasses = array();
        if (!isset($cachedReflectionClasses[$className])) {
            $cachedReflectionClasses[$className] =
                new \ReflectionClass($className);
        }
        return $cachedReflectionClasses[$className];
    }

    public function getFieldDefs()
    {
        return $this->fieldDefs;
    }

    public function getFieldMap()
    {
        return $this->fieldMap;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Хук события "перед преобразованием в объект"
     * @param array $array Ссылка на массив данных
     * @return null
     */
    protected function preHydrate(array $array)
    {

    }

    /**
     * Хук события "после преобразования в объект"
     * @param object $object
     * @return null
     */
    protected function postHydrate($object)
    {

    }

    /**
     * Хук события "перед преобразованием в массив"
     * @param object $object
     * @return null
     */
    protected function preUnhydrate($object)
    {

    }

    /**
     * Хук события "после преобразования в массив"
     * @param array $array Ссылка на массив данных
     * @return null
     */
    protected function postUnhydrate(array $array)
    {

    }

    public function getMappedField($propName)
    {
        if (!isset($this->fieldMap[$propName])) {
            throw new \RuntimeException(
                sprintf('Не найден мэппинг для свойства "%s"', $propName)
            );
        }
        return $this->fieldMap[$propName];
    }

    private function remapFieldNamesInCriteria(array &$criteria)
    {
        foreach ($criteria['filter'] as &$alternative) {
            foreach ($alternative as &$condition) {
                $condition['field'] =
                    $this->getMappedField($condition['field']);
            }
        }
        $order = array();
        foreach ($criteria['order'] as $fieldName => $dir) {
            $order[$this->getMappedField($fieldName)] = $dir;
        }
        $criteria['order'] = $order;
    }

}
