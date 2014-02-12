<?php

namespace Homebase\Service;

class Delayed
{
    protected $queue;

    protected $remoteHue;

    public function __construct($queue, $remoteHue)
    {
        $this->queue = $queue;
        $this->remoteHue = $remoteHue;
    }

    public function run()
    {
        $to = new \DateTime('-1 minute');
        $lights = $this->queue->getLights($to->format('Y-m-d H:i:s'));

        foreach ($lights as $light) {
            $this->remoteHue->setLightState($light['light'], array('on' => false));
            $this->queue->deleteLight($light['light']);
        }
    }
}
