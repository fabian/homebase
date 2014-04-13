<?php

namespace Homebase\Service;

use Homebase\Service\Engine;

class EngineTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->beacons = $this->getMockBuilder('Homebase\Service\Beacons')
            ->disableOriginalConstructor()
            ->getMock();
        $this->lights = $this->getMockBuilder('Homebase\Service\Lights')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder('Homebase\Service\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->engine = new Engine($this->beacons, $this->lights, $this->config);
    }

    public function testRun()
    {
        $this->config->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('engine_mode')
            )
            ->will($this->returnValue('automatic'));

        $this->beacons->expects($this->once())
            ->method('getLatestStates')
            ->will($this->returnValue(array(
                array('id' => '1', 'state' => 'inside'),
                array('id' => '2', 'state' => 'outside'),
            )));

        $this->beacons->expects($this->once())
            ->method('getMappings')
            ->will($this->returnValue(array(
                array('light' => 1, 'beacon' => '1'),
                array('light' => 2, 'beacon' => '2'),
                array('light' => 3, 'beacon' => null),
            )));

        $this->lights->expects($this->at(0))
            ->method('getLatestActions')
            ->will($this->returnValue(array(
            )));

        $this->lights->expects($this->at(1))
            ->method('updateActions')
            ->with(
                $this->equalTo('1'),
                $this->equalTo(false),
                $this->equalTo('queued'),
                $this->equalTo('canceled')
            );

        $this->lights->expects($this->at(2))
            ->method('addAction')
            ->with(
                $this->equalTo('1'),
                $this->equalTo(true)
            );

        $this->lights->expects($this->at(3))
            ->method('addAction')
            ->with(
                $this->equalTo('2'),
                $this->equalTo(false),
                $this->equalTo(180)
            );

        $this->lights->expects($this->at(4))
            ->method('addAction')
            ->with(
                $this->equalTo('3'),
                $this->equalTo(false),
                $this->equalTo(180)
            );

        $this->engine->run();
    }

    public function testNotRun()
    {
        $this->config->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('engine_mode')
            )
            ->will($this->returnValue('manual'));

        $this->beacons->expects($this->never())
            ->method($this->anything());

        $this->lights->expects($this->never())
            ->method($this->anything());

        $this->engine->run();
    }
}
