<?php

namespace Homebase\Service;

class RemoteHue implements Hue
{
    protected $bridgeId;

    protected $accessToken;

    protected $client;

    public function __construct($client, $bridgeId, $accessToken)
    {
        $this->client = $client;
        $this->bridgeId = $bridgeId;
        $this->accessToken = $accessToken;
    }

    public function sendMessage($message)
    {
        $params = array(
            'token' => $this->accessToken,
        );

        $data = array(
            'clipmessage' => json_encode($message)
        );

        $request = $this->client->post('sendmessage', array(), $data,  array('query' => $params));

        // send request
        $response = $request->send();

        // parse response
        $data = $response->json();

        return $data;
    }

    public function setLightState($lightId, $state)
    {
        $message = array(
            'bridgeId' => $this->bridgeId,
            'clipCommand' => array(
                'url' => '/api/0/lights/' . $lightId . '/state',
                'method' => 'PUT',
                'body' => $state,
            )
        );

        return $this->sendMessage($message);
    }

    public function getBridgeInfo()
    {
        $params = array(
            'token' => $this->accessToken,
            'bridgeid' => $this->bridgeId,
        );

        $request = $this->client->get('getbridge', array(), array('query' => $params));

        // send request
        $response = $request->send();

        // parse response
        $data = $response->json();

        return $data;
    }
}
