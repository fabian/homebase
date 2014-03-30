<?php

namespace Homebase\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BeaconsController
{
    protected $beacons;

    protected $engine;

    protected $oauth;

    public function __construct($beacons, $engine, $oauth) {
        $this->beacons = $beacons;
        $this->engine = $engine;
        $this->oauth = $oauth;
    }

    public function addProximity(Request $request)
    {
        $accessToken = $request->get('access_token', '');
        $uuid = $request->get('uuid', '');
        $major = $request->get('major', '');
        $minor = $request->get('minor', '');
        $accuracy = $request->get('accuracy', 0);
        $proximity = $request->get('proximity', '');
        $rssi = $request->get('rssi', 0);
        $occurred = $request->get('occurred');
        $positionX = $request->get('x', null);
        $positionY = $request->get('y', null);

        $occurredDateTime = new \DateTime($occurred);
        $occurred = $occurredDateTime->format('Y-m-d H:i:s');
        $occurredMicro = $occurredDateTime->format('u');

        $this->checkAccessToken($accessToken);

        // make sure beacon exists
        $this->beacons->addBeacon($uuid, $major, $minor);

        // get beacon id
        $beacon = $this->beacons->getBeacon($uuid, $major, $minor);

        $this->beacons->addProximity($beacon['id'], $accuracy, $proximity, $rssi, $occurred, $occurredMicro, $positionX, $positionY);

        return new JsonResponse();
    }

    public function addState(Request $request)
    {
        $accessToken = $request->get('access_token', '');
        $uuid = $request->get('uuid', '');
        $major = $request->get('major', '');
        $minor = $request->get('minor', '');
        $state = $request->get('state', '');
        $occurred = $request->get('occurred');

        $occurredDateTime = new \DateTime($occurred);
        $occurred = $occurredDateTime->format('Y-m-d H:i:s');
        $occurredMicro = $occurredDateTime->format('u');

        $this->checkAccessToken($accessToken);

        // make sure beacon exists
        $this->beacons->addBeacon($uuid, $major, $minor);

        // get beacon id
        $beacon = $this->beacons->getBeacon($uuid, $major, $minor);

        $this->beacons->addState($beacon['id'], $state, $occurred, $occurredMicro);

        // run engine
        $this->engine->run();

        return new JsonResponse();
    }

    public function getBeacon($uuid, $major, $minor)
    {
        $beacon = $this->beacons->getBeacon($uuid, $major, $minor);

        return new JsonResponse($beacon);
    }

    protected function checkAccessToken($token)
    {
        $accessToken = $this->oauth->getAccessToken($token);
        if (!$accessToken) {
            throw new AccessDeniedHttpException('Invalid or missing access token');
        }
    }
}
