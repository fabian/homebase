<?php

namespace Homebase\Service;

class Sync
{
    protected $remoteHue;

    protected $lights;

    public function __construct($remoteHue, $lights)
    {
        $this->remoteHue = $remoteHue;
        $this->lights = $lights;
    }

    public function run()
    {
        $info = $this->remoteHue->getBridgeInfo();

        foreach ($info['lights'] as $number => $lightInfo) {

            // make sure light exists
            $this->lights->addLight($number, $lightInfo['name']);

            // get light id
            $light = $this->lights->getLight($number);

            // log state
            $this->lights->addLog($light['id'], $lightInfo['state']['reachable'] && $lightInfo['state']['on']);
        }
    }
}
