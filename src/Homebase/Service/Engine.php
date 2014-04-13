<?php

namespace Homebase\Service;

class Engine
{
    const STATE_INSIDE = 'inside';

    protected $beacons;

    protected $lights;

    protected $config;

    public function __construct($beacons, $lights, $config)
    {
        $this->beacons = $beacons;
        $this->lights = $lights;
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

        $actions = $this->lights->getLatestActions();
        $actionsGrouped = array();
        foreach ($actions as $action) {
            $actionsGrouped[$action['light']] = $action['on'];
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

                // only switch on if not already switched on
                if (!isset($actionsGrouped[$light]) || !$actionsGrouped[$light]) {

                    // remove light from queue, avoid off
                    $this->lights->updateActions($light, false, Lights::STATE_QUEUED, Lights::STATE_CANCELED);

                    // add action to switch on (now)
                    $this->lights->addAction($light, $on, 0);
                }

            } else {

                // only switch off if not already switched off
                if (!isset($actionsGrouped[$light]) || $actionsGrouped[$light]) {

                    // add queued off action (3min)
                    $this->lights->addAction($light, $on, 3 * 60);

                }
            }
        }
    }
}
