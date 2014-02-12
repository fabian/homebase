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
        $uuid = $request->get('uuid', '');
        $major = $request->get('major', '');
        $minor = $request->get('minor', '');
        $accuracy = $request->get('accuracy', 0);
        $proximity = $request->get('proximity', '');
        $rssi = $request->get('rssi', 0);

        // make sure beacon exists
        $this->beacons->addBeacon($uuid, $major, $minor);

        // get beacon id
        $beacon = $this->beacons->getBeacon($uuid, $major, $minor);

        $this->beacons->addProximity($beacon['id'], $accuracy, $proximity, $rssi);

        return new JsonResponse();
    }

    public function addState(Request $request)
    {
        $uuid = $request->get('uuid', '');
        $major = $request->get('major', '');
        $minor = $request->get('minor', '');
        $state = $request->get('state', '');

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
