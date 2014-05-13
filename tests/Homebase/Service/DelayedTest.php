<?php

namespace Homebase\Service;

use Homebase\Service\Delayed;

class DelayedTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->lights = $this->getMockBuilder('Homebase\Service\Lights')
            ->disableOriginalConstructor()
            ->getMock();
        $this->remoteHue = $this->getMockBuilder('Homebase\Service\RemoteHue')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder('Homebase\Service\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->delayed = new Delayed($this->lights, $this->remoteHue, $this->config);
    }

    public function testRun()
    {
        $this->lights->expects($this->at(0))
            ->method('getQueuedActions')
            ->will($this->returnValue(array(
                array('id' => 1, 'on' => '1', 'number' => '3'),
                array('id' => 2, 'on' => '0', 'number' => '4'),
            )));

        $this->config->expects($this->exactly(2))
            ->method('get')
            ->with(
                $this->equalTo('engine_mode')
            )
            ->will($this->returnValue('automatic'));

        $this->remoteHue->expects($this->at(0))
            ->method('setLightState')
            ->with(
                $this->equalTo('3'),
                $this->equalTo(array('on' => true))
            );

        $this->lights->expects($this->at(1))
            ->method('updateAction')
            ->with(
                $this->equalTo(1),
                $this->equalTo('executed')
            );

        $this->remoteHue->expects($this->at(1))
            ->method('setLightState')
            ->with(
                $this->equalTo('4'),
                $this->equalTo(array('on' => false))
            );

        $this->lights->expects($this->at(2))
            ->method('updateAction')
            ->with(
                $this->equalTo(2),
                $this->equalTo('executed')
            );

        $this->delayed->run();
    }

    public function testNotRun()
    {
        $this->lights->expects($this->once())
            ->method('getQueuedActions')
            ->will($this->returnValue(array(
                array('id' => 1, 'on' => '1', 'number' => '1'),
            )));
    
        $this->config->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('engine_mode')
            )
            ->will($this->returnValue('manual'));

        $this->lights->expects($this->once())
            ->method('updateAction')
            ->with(
                $this->equalTo(1),
                $this->equalTo('executed')
            );

        $this->remoteHue->expects($this->never())
            ->method($this->anything());

        $this->delayed->run();
    }
}
