<?php

namespace Homebase\Service;

interface Hue {

    public function setLightState($lightId, $state);

    public function getBridgeInfo();
}
