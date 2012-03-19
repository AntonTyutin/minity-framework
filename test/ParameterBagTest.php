<?php

require_once 'vfsStream/vfsStream.php'; // https://github.com/mikey179/vfsStream/wiki/Install

use Minity\Util\MultilevelBag;

/**
 * Test class for MultilevelBag.
 * Generated by PHPUnit on 2012-03-09 at 02:41:15.
 */
class ParameterBagTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Minity\Util\MultilevelBag
     */
    protected $config;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        vfsStream::setup('root');
        $this->config = new MultilevelBag();
    }

    /**
     * @dataProvider setAndGetProvider
     *
     * @param $setKey
     * @param $setValue
     * @param $default
     * @param $getKey
     * @param $expected
     */
    public function testSetAndGet(
        $setKey, $setValue, $default,
        $getKey, $expected
    )
    {
        $this->config->set($setKey, $setValue);
        $this->assertEquals($expected, $this->config->get($getKey, $default));
    }

    /**
     * @return array
     */
    public function setAndGetProvider()
    {
        return array(
            array('some.param.name', 10, null, 'some.param.name', 10),
            array('some.param.name', 10, null, 'some.param.name2', null),
            array('some.param.name', 10, 1, 'some.param.name2', 1),
        );
    }

    /**
     *
     */
    public function testCreateFromFile()
    {
        $filename = vfsStream::url('root') . '/config.php';
        $configuration = array(
            'some' => array(
                'param' => array(
                    'name' => 10
                )
            )
        );
        $this->saveConfig($filename, $configuration);
        $config = MultilevelBag::createFromFile($filename);
        $this->assertInstanceOf('Minity\Util\MultilevelBag', $config);
        $this->assertEquals(10, $config->get('some.param.name'));
        $this->assertEquals(array('name' => 10), $config->get('some.param'));
    }

    /**
     *
     */
    public function testToArray()
    {
        $expected = array(
            'some' => array(
                'param' => array(
                    'name' => 10
                )
            )
        );
        $config = new MultilevelBag();
        $config->set('some.param.name', 10);
        $this->assertEquals($expected, $config->toArray());
    }

    /**
     *
     */
    public function testMerge()
    {
        $config1 = new MultilevelBag();
        $config2 = new MultilevelBag();
        $config1->set('param.config1.name', 'value');
        $config2->set('param.config1.name', 'value1');
        $config2->set('param.config2.name', 'value2');
        $config1->merge($config2);
        $expected = array(
            'param' => array(
                'config1' => array(
                    'name' => 'value1'
                ),
                'config2' => array(
                    'name' => 'value2'
                ),
            )
        );
        $this->assertEquals($expected, $config1->toArray());
    }

    /**
     * @param $filename
     * @param $configuration
     */
    private function saveConfig($filename, $configuration)
    {
        file_put_contents(
            $filename,
            '<?php return ' . var_export($configuration, true) . ';'
        );
    }

}
