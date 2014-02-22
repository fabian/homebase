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
        $this->queue = $this->getMockBuilder('Homebase\Service\Queue')
            ->disableOriginalConstructor()
            ->getMock();
        $this->engine = new Engine($this->beacons, $this->remoteHue, $this->queue);
    }

    public function testRun()
    {
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

        $this->queue->expects($this->once())
            ->method('deleteLight')
            ->with(
                $this->equalTo('1')
            );

        $this->remoteHue->expects($this->once())
            ->method('setLightState')
            ->with(
                $this->equalTo('1'),
                $this->equalTo(array('on' => true))
            );

        $this->queue->expects($this->once())
            ->method('addLight')
            ->with(
                $this->equalTo('2')
            );

        $this->engine->run();
    }
}
