<?php

namespace Homebase\Service;

class SyncTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->hue = $this->getMockBuilder('Homebase\Service\Hue')
            ->disableOriginalConstructor()
            ->getMock();
        $this->lights = $this->getMockBuilder('Homebase\Service\Lights')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sync = new Sync($this->hue, $this->lights);
    }

    public function testRun()
    {
        $this->hue->expects($this->once())
            ->method('getBridgeInfo')
            ->will($this->returnValue(array(
                'lights' => array(
                    '1' => array('name' => 'Test', 'state' => array('reachable' => true, 'on' => true)),
                    '2' => array('name' => 'Foobar', 'state' => array('reachable' => true, 'on' => false)),
                ),
            )));

        $i = 0;

        $this->lights->expects($this->at($i++))
            ->method('addLight')
            ->with(
                $this->equalTo('1'),
                $this->equalTo('Test')
            );

        $this->lights->expects($this->at($i++))
            ->method('getLight')
            ->with(
                $this->equalTo('1')
            )
            ->will($this->returnValue(array('id' => 1)));

        $this->lights->expects($this->at($i++))
            ->method('addLog')
            ->with(
                $this->equalTo(1),
                $this->equalTo(true)
            );

        $this->lights->expects($this->at($i++))
            ->method('addLight')
            ->with(
                $this->equalTo('2'),
                $this->equalTo('Foobar')
            );

        $this->lights->expects($this->at($i++))
            ->method('getLight')
            ->with(
                $this->equalTo('2')
            )
            ->will($this->returnValue(array('id' => 2)));

        $this->lights->expects($this->at($i++))
            ->method('addLog')
            ->with(
                $this->equalTo(2),
                $this->equalTo(false)
            );

        $this->sync->run();
    }

    public function testNotRun()
    {
        $this->hue->expects($this->once())
            ->method('getBridgeInfo')
            ->will($this->returnValue(array(
                'lights' => array(),
            )));

        $this->lights->expects($this->never())
            ->method($this->anything());

        $this->sync->run();
    }
}
