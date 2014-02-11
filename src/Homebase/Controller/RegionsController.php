<?php

namespace Homebase\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RegionsController
{
    protected $regions;

    public function __construct($regions) {
        $this->regions = $regions;
    }

    public function addRegion(Request $request)
    {
        $this->regions->addRegion($request->request);

        return new JsonResponse();
    }
}
