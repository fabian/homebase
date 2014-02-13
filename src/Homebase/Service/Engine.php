<?php

namespace Homebase\Service;

class Engine
{
    const STATE_INSIDE = 'inside';

    private $mapping = array(
        '1' => array('8'),
        '2' => array('6', '7', '2', '3', '4'),
        '3' => array('1', '5', '4', '3', '2'),
    );

    protected $beacons;

    protected $remoteHue;

    protected $queue;

    public function __construct($beacons, $remoteHue, $queue)
    {
        $this->beacons = $beacons;
        $this->remoteHue = $remoteHue;
        $this->queue = $queue;
    }

    public function run()
    {
        $states = $this->beacons->getLatestStates();

        $lights = array();
        foreach ($states as $state) {
            
            $beaconId = $state['beacon'];
            
            if (isset($this->mapping[$beacon])) {
                
                foreach ($this->mapping[$beacon] as $light) {
                    
                    if ($state['state'] == self::STATE_INSIDE) {
                        
                        $lights[$light] = true;
                        
                    } else {
                        
                        // don't override value
                        if (!isset($lights[$light])) {
                            $lights[$light] = false;
                        }
                    }
                }
            }
        }
        
        foreach ($lights as $light => $on) {

            if ($on) {

                // remove light from queue, avoid off
                $this->queue->deleteLight($light);

                // switch light on
                $this->remoteHue->setLightState($light, array('on' => $on));

            } else {

                // add light to queue for delayed off
                $this->queue->addLight($light);

            }
        }
    }
}
