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
        $this->remoteHue = $this->getMockBuilder('Homebase\Service\RemoteHue')
            ->disableOriginalConstructor()
            ->getMock();
        $this->lights = $this->getMockBuilder('Homebase\Service\Lights')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder('Homebase\Service\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->engine = new Engine($this->beacons, $this->remoteHue, $this->lights, $this->config);
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
                array('beacon' => '1', 'light' => '1'),
                array('beacon' => '2', 'light' => '2'),
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
                $this->equalTo('cancelled')
            );

        $this->lights->expects($this->at(2))
            ->method('addAction')
            ->with(
                $this->equalTo('1'),
                $this->equalTo(true)
            );

        $this->remoteHue->expects($this->once())
            ->method('setLightState')
            ->with(
                $this->equalTo('1'),
                $this->equalTo(array('on' => true))
            );

        $this->lights->expects($this->at(3))
            ->method('updateActions')
            ->with(
                $this->equalTo('1'),
                $this->equalTo(true),
                $this->equalTo('queued'),
                $this->equalTo('executed')
            );

        $this->lights->expects($this->at(4))
            ->method('addAction')
            ->with(
                $this->equalTo('2'),
                $this->equalTo(false)
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

        $this->remoteHue->expects($this->never())
            ->method($this->anything());

        $this->engine->run();
    }
}
