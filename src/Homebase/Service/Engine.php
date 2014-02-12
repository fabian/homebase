<?php

namespace Homebase\Service;

class Engine
{
    const STATE_INSIDE = 'inside';

    private $mapping = array(
        'B9407F30-F5F8-466E-AFF9-25556B57FE6D.9657.15660' => array('8'),
        'B9407F30-F5F8-466E-AFF9-25556B57FE6D.21023.64576' => array('6', '7', '2', '3', '4'),
        'FFFFFFFF-FFFF-FFFF-FFFF-FFFFFFFFFFFF.0.65535' => array('1', '5', '4', '3', '2'),
    );

    protected $regions;

    protected $remoteHue;

    public function __construct($regions, $remoteHue)
    {
        $this->regions = $regions;
        $this->remoteHue = $remoteHue;
    }

    public function run()
    {
        $states = $this->regions->getRegionStates();

        $lights = array();
        foreach ($states as $state) {
            
            $beacon = $state['uuid'] . '.' . $state['major'] . '.' . $state['minor'];
            
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
            $this->remoteHue->setLightState($light, array('on' => $on));
        }
    }
}
