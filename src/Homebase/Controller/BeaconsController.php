<?php

namespace Homebase\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BeaconsController
{
    protected $beacons;

    public function __construct($beacons) {
        $this->beacons = $beacons;
    }

    public function addBeacon(Request $request)
    {
        $this->beacons->addBeacon($request->request);

        return new JsonResponse();
    }

    public function getBeacon($uuid)
    {
        $beacon = $this->beacons->getBeacon($uuid);

        return new JsonResponse($beacon);
    }
}
