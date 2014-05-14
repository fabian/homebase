<?php

namespace Homebase\Service;

class LocalHue implements Hue
{
    protected $bridgeId;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function setLightState($lightId, $state)
    {
        $request =  $this->client->put('lights/' . $lightId . '/state', array(), json_encode($state));

        // send request
        $response = $request->send();

        // parse response
        $data = $response->json();

        return $data;
    }

    public function getBridgeInfo()
    {
        $request = $this->client->get('');

        // send request
        $response = $request->send();

        // parse response
        $data = $response->json();

        return $data;
    }
}
