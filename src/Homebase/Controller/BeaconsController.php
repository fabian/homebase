<?php

namespace Homebase\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BeaconsController
{
    protected $beacons;

    protected $oauth;

    public function __construct($beacons, $oauth) {
        $this->beacons = $beacons;
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
        $power = $request->get('power', null);

        $occurredDateTime = new \DateTime($occurred);
        $occurred = $occurredDateTime->format('Y-m-d H:i:s');
        $occurredMicro = $occurredDateTime->format('u');

        $this->checkAccessToken($accessToken);

        // make sure beacon exists
        $this->beacons->addBeacon($uuid, $major, $minor);

        // get beacon id
        $beacon = $this->beacons->getBeacon($uuid, $major, $minor);

        $this->beacons->addProximity($beacon['id'], $accuracy, $proximity, $rssi, $occurred, $occurredMicro, $positionX, $positionY, $power);

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

        return new JsonResponse();
    }

    public function getBeacons(Request $request)
    {
        $accessToken = $request->get('access_token', '');
        $this->checkAccessToken($accessToken);

        $beacons = $this->beacons->getBeacons();

        $data = array();
        foreach ($beacons as $beacon) {
            $data[] = array(
                'id' => (int) $beacon['id'],
                'uuid' => $beacon['uuid'],
                'major' => (int) $beacon['major'],
                'minor' => (int) $beacon['minor'],
                'name' => $beacon['name'],
                'active' => (bool) $beacon['active'],
            );
        }

        return new JsonResponse(array('beacons' => $data));
    }

    public function getBeacon($uuid, $major, $minor, Request $request)
    {
        $accessToken = $request->get('access_token', '');
        $this->checkAccessToken($accessToken);

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
