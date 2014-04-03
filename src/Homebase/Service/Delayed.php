<?php

namespace Homebase\Service;

class Delayed
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
        $to = new \DateTime('-3 minute');
        $lights = $this->lights->getQueuedActions(false, $to->format('Y-m-d H:i:s'));

        foreach ($lights as $light) {

            // only switch lights off if engine is running
            if ($this->config->get(Config::ENGINE_MODE) != Config::ENGINE_MODE_MANUAL) {

                $this->remoteHue->setLightState($light['light'], array('on' => false));
            }

            // always remove light from queue
            $this->lights->updateActions($light['light'], false, Lights::STATE_QUEUED, Lights::STATE_EXECUTED);
        }
    }
}
