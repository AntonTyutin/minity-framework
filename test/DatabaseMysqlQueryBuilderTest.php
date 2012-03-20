<?php

use Minity\Database\Mysql\QueryBuilder;
use Minity\Database\CriteriaBuilder;

/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */
class DatabaseMysqlQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function failIfNotConnectedToDatabase()
    {
        if (null === @mysql_info()) {
            $this->fail('Нет подключения к mysql для экранирования символов');
        }
    }

    /**
     * @dataProvider provideOperations
     */
    public function testOperations($operation, $value, $expected)
    {
        $this->failIfNotConnectedToDatabase();
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from('test')
            ->where(
                array(
                     array( // alertnative
                         array(
                             'field'     => 'a',
                             'operation' => $operation,
                             'value'     => $value
                         )
                     ),
                )
            );
        $expectedSql = 'select * from `test` where `a` ' . $expected;
        $this->assertEquals(
            $expectedSql,
            $builder->build()
        );
    }

    public function provideOperations()
    {
        return array(
            array(CriteriaBuilder::OPERATION_EQUALS, 0, '= 0'),
            array(CriteriaBuilder::OPERATION_GREATER_OR_EQUALS, 0, '>= 0'),
            array(CriteriaBuilder::OPERATION_LESS_OR_EQUALS, 0, '<= 0'),
            array(CriteriaBuilder::OPERATION_GREATER_THEN, 0, '> 0'),
            array(CriteriaBuilder::OPERATION_LESS_THEN, 0, '< 0'),
            array(CriteriaBuilder::OPERATION_NOT_EQUALS, 0, '<> 0'),
            array(CriteriaBuilder::OPERATION_IN, array(0, '0'), 'in(0, "0")'),
        );
    }

    public function testSelect()
    {
        $this->failIfNotConnectedToDatabase();

        $builder = new QueryBuilder();
        $builder
            ->select('*', 'field 1', 'field"', '@max( field3,field4 )')
            ->from('test table')
            ->where(
                array(
                    array( // alertnative
                        array(
                            'field' => '`id`',
                            'operation' => CriteriaBuilder::OPERATION_IN,
                            'value' => array(100, '()')
                        )
                    ),
                    array( // alertnative
                        array(
                            'field' => 'rec code',
                            'operation' => CriteriaBuilder::OPERATION_EQUALS,
                            'value' => '1"; delete * from users;'
                        )
                    )
                )
            )
            ->group('field 1', 'field 2')
            ->offset(10)
            ->limit(1);
        $expectedSql = <<<SQL
            select
                *, `field 1`, `field\"`, max(`field3`, `field4`) max
            from
                `test table`
            where
                `\`id\`` in(100, "()")
                    or `rec code` = "1\"; delete * from users;"
            group by `field 1`, `field 2`
            limit 1 offset 10
SQL;
        $expectedSql = trim(preg_replace('/\s+/', ' ', $expectedSql));
        $this->assertEquals(
            $expectedSql,
            $builder->build()
        );
    }

    public function testUpdate()
    {
        $this->failIfNotConnectedToDatabase();

        $builder = new QueryBuilder();
        $builder
            ->update(array('field 1' => '1; select now();', 'field"' => 3.1428))
            ->in('test table')
            ->where(
                array(
                    array( // alertnative
                        array(
                            'field' => '`id`',
                            'operation' => CriteriaBuilder::OPERATION_IN,
                            'value' => array(100, '()')
                        )
                    ),
                    array( // alertnative
                        array(
                            'field' => 'rec code',
                            'operation' => CriteriaBuilder::OPERATION_EQUALS,
                            'value' => '1"; delete * from users;'
                        )
                    )
                )
            )
            ->offset(10)
            ->limit(1);
        $expectedSql = <<<SQL
            update
                `test table`
            set
                `field 1` = "1; select now();", `field\"` = 3.1428
            where
                `\`id\`` in(100, "()")
                    or `rec code` = "1\"; delete * from users;"
            limit 1 offset 10
SQL;
        $expectedSql = trim(preg_replace('/\s+/', ' ', $expectedSql));
        $this->assertEquals(
            $expectedSql,
            $builder->build()
        );
    }

    public function testInsert()
    {
        $this->failIfNotConnectedToDatabase();

        $builder = new QueryBuilder();
        $builder
            ->insert(array('field 1' => '1; select now();', 'field"' => 3.1428))
            ->into('test table');
        $expectedSql = <<<SQL
            insert into
                `test table`
            set
                `field 1` = "1; select now();", `field\"` = 3.1428
SQL;
        $expectedSql = trim(preg_replace('/\s+/', ' ', $expectedSql));
        $this->assertEquals(
            $expectedSql,
            $builder->build()
        );
    }

    public function testDelete()
    {
        $this->failIfNotConnectedToDatabase();

        $builder = new QueryBuilder();
        $builder
            ->delete('test table')
            ->where(
                array(
                    array( // alertnative
                        array(
                            'field' => '`id`',
                            'operation' => CriteriaBuilder::OPERATION_IN,
                            'value' => array(100, '()')
                        )
                    ),
                    array( // alertnative
                        array(
                            'field' => 'rec code',
                            'operation' => CriteriaBuilder::OPERATION_EQUALS,
                            'value' => '1"; delete * from users;'
                        )
                    )
                )
            )
            ->limit(100);
        $expectedSql = <<<SQL
            delete from
                `test table`
            where
                `\`id\`` in(100, "()")
                    or `rec code` = "1\"; delete * from users;"
            limit 100
SQL;
        $expectedSql = trim(preg_replace('/\s+/', ' ', $expectedSql));
        $this->assertEquals(
            $expectedSql,
            $builder->build()
        );
    }

}
