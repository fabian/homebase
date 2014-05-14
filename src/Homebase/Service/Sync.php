<?php

namespace Homebase\Service;

class Sync implements Runnable
{
    protected $hue;

    protected $lights;

    public function __construct($hue, $lights)
    {
        $this->hue = $hue;
        $this->lights = $lights;
    }

    public function run()
    {
        $info = $this->hue->getBridgeInfo();

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
