<?php

namespace Homebase\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BeaconsController
{
    protected $beacons;

    protected $engine;

    public function __construct($beacons, $engine) {
        $this->beacons = $beacons;
        $this->engine = $engine;
    }

    public function addProximity(Request $request)
    {
        $uuid = $beacon->get('uuid', '');
        $major = $beacon->get('major', '');
        $minor = $beacon->get('minor', '');
        $accuracy = $beacon->get('accuracy', 0);
        $proximity = $beacon->get('proximity', '');
        $rssi = $beacon->get('rssi', 0);

        // make sure beacon exists
        $this->beacons->addBeacon($uuid, $major, $minor);

        // get beacon id
        $beacon = $this->beacons->getBeacon($uuid, $major, $minor);

        $this->beacons->addBeacon($beacon['id'], $accuracy, $proximity, $rssi);

        return new JsonResponse();
    }

    public function addState(Request $request)
    {
        $uuid = $beacon->get('uuid', '');
        $major = $beacon->get('major', '');
        $minor = $beacon->get('minor', '');
        $state = $beacon->get('state', '');

        // make sure beacon exists
        $this->beacons->addBeacon($uuid, $major, $minor);

        // get beacon id
        $beacon = $this->beacons->getBeacon($uuid, $major, $minor);

        $this->beacons->addState($beacon['id'], $state);

        // run engine
        $this->engine->run();

        return new JsonResponse();
    }

    public function getBeacon($uuid, $major, $minor)
    {
        $beacon = $this->beacons->getBeacon($uuid, $major, $minor);

        return new JsonResponse($beacon);
    }
}
