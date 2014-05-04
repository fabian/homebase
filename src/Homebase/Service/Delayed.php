<?php

namespace Homebase\Service;

class Delayed implements Runnable
{
    protected $lights;

    protected $remoteHue;

    protected $config;

    public function __construct($lights, $remoteHue, $config)
    {
        $this->lights = $lights;
        $this->remoteHue = $remoteHue;
        $this->config = $config;
    }

    public function run()
    {
        $lights = $this->lights->getQueuedActions();

        foreach ($lights as $light) {

            // only switch lights if engine is running
            if ($this->config->get(Config::ENGINE_MODE) != Config::ENGINE_MODE_MANUAL) {

                $this->remoteHue->setLightState($light['number'], array('on' => (boolean) $light['on']));
            }

            // always remove light from queue
            $this->lights->updateAction($light['id'], Lights::STATE_EXECUTED);
        }
    }
}
