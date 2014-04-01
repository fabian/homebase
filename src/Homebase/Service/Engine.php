<?php

namespace Homebase\Service;

class Engine
{
    const STATE_INSIDE = 'inside';

    protected $beacons;

    protected $remoteHue;

    protected $queue;

    protected $config;

    public function __construct($beacons, $remoteHue, $queue, $config)
    {
        $this->beacons = $beacons;
        $this->remoteHue = $remoteHue;
        $this->queue = $queue;
        $this->config = $config;
    }

    public function run()
    {
        if ($this->config->get(Config::ENGINE_MODE) == Config::ENGINE_MODE_MANUAL) {
            // don't run engine
            return;
        }

        $states = $this->beacons->getLatestStates();
        $mappings = $this->beacons->getMappings();

        $mappingGrouped = array();
        foreach ($mappings as $mapping) {
            if (!isset($mappingGrouped[$mapping['beacon']])) {
                $mappingGrouped[$mapping['beacon']] = array();
            }
            $mappingGrouped[$mapping['beacon']][$mapping['light']] = true;
        }

        $lights = array();
        foreach ($states as $state) {

            $beaconId = $state['id'];

            if (isset($mappingGrouped[$beaconId])) {

                foreach ($mappingGrouped[$beaconId] as $light => $true) {

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
