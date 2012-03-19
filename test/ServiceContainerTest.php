<?php

use Minity\Service\Container;
use Minity\Util\MultilevelBag;

/**
 * Test class for Container.
 * Generated by PHPUnit on 2012-03-09 at 13:14:13.
 */
class ServiceContainerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Minity\Service\Container
     */
    protected $container;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->container = new Container();
    }

    public function testSetAndGetService()
    {
        $service = new stdClass();
        $this->container->set('some.service', $service);
        $this->assertSame($service, $this->container->get('some.service'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetUnexistentService()
    {
        $this->container->get('some.service');
    }

    public function testLoad()
    {
        include __DIR__ . '/mock/ServiceMock.php';
        $conf = array(
            'some.service' => array(
                'class' => 'ServiceMock',
                'args'  => array('@some.service.param', 'twelve')
            ),
            'another.service' => array(
                'class' => 'ServiceMock',
                'args'  => array('@some.service')
            )
        );
        $params = new MultilevelBag();
        $params->set('some.service.param', '100 usd');
        $this->container->setParams($params);
        $this->container->load($conf);
        $this->assertInstanceOf(
            'ServiceMock',
            $this->container->get('some.service')
        );
        $this->assertEquals(
            array($params->get('some.service.param'), 'twelve'),
            $this->container->get('some.service')->arguments
        );
        $this->assertSame(
            $this->container->get('some.service'),
            $this->container->get('another.service')->arguments[0]
        );
    }
}
