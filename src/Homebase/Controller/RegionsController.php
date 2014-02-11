<?php

namespace Homebase\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RegionsController
{
    protected $regions;

    protected $engine;

    public function __construct($regions, $engine) {
        $this->regions = $regions;
        $this->engine = $engine;
    }

    public function addRegion(Request $request)
    {
        $this->regions->addRegion($request->request);

        $this->engine->run();

        return new JsonResponse();
    }
}
