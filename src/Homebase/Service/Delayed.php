<?php

namespace Homebase\Service;

class Delayed
{
    protected $queue;

    protected $remoteHue;

    protected $config;

    public function __construct($queue, $remoteHue, $config)
    {
        $this->queue = $queue;
        $this->remoteHue = $remoteHue;
        $this->config = $config;
    }

    public function run()
    {
        $to = new \DateTime('-3 minute');
        $lights = $this->queue->getLights($to->format('Y-m-d H:i:s'));

        foreach ($lights as $light) {

            // only switch lights off if engine is running
            if ($this->config->get(Config::ENGINE_MODE) == Config::ENGINE_MODE_AUTOMATIC) {

                $this->remoteHue->setLightState($light['light'], array('on' => false));
            }

            // always remove light from queue
            $this->queue->deleteLight($light['light']);
        }
    }
}
