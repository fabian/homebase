<?php

namespace Homebase\Controller;

use Symfony\Component\HttpFoundation\Request;

class BeaconsControllerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->uuid = 'A78DD916-39D6-4B7D-90E3-29752EB3A7B5';
        $this->major = '21234';
        $this->minor = '59872';
        $this->state = 'inside';

        $this->request = new Request(array(
            'access_token' => '7b71d9fdfe5b',
            'uuid' => $this->uuid,
            'major' => $this->major,
            'minor' => $this->minor,
            'state' => $this->state,
            'occurred' => '2014-02-11 16:44:59.588000',
        ));

        $this->beacons = $this->getMockBuilder('Homebase\Service\Beacons')
            ->disableOriginalConstructor()
            ->getMock();
        $this->oauth = $this->getMockBuilder('Homebase\Service\OAuth')
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new BeaconsController($this->beacons, $this->oauth);
    }

    public function testAddState()
    {
        $this->oauth->expects($this->once())
            ->method('getAccessToken')
            ->will($this->returnValue(array(
                'id' => 1,
            )));

        $this->beacons->expects($this->at(0))
            ->method('addBeacon')
            ->with(
                $this->equalTo($this->uuid),
                $this->equalTo($this->major),
                $this->equalTo($this->minor)
            );

        $this->beacons->expects($this->at(1))
            ->method('getBeacon')
            ->with(
                $this->equalTo($this->uuid),
                $this->equalTo($this->major),
                $this->equalTo($this->minor)
            )
            ->will($this->returnValue(array(
                'id' => 1,
            )));

        $this->beacons->expects($this->at(2))
            ->method('addState')
            ->with(
                $this->equalTo(1),
                $this->equalTo($this->state),
                $this->equalTo('2014-02-11 16:44:59'),
                $this->equalTo('588000')
            );

        $this->controller->addState($this->request);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function testAddStateUnauthorized()
    {
        $this->oauth->expects($this->once())
            ->method('getAccessToken')
            ->will($this->returnValue(null));

        $this->controller->addState($this->request);
    }
}
