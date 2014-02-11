<?php

namespace Homebase\Service;

class RemoteHue
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

    public function setLightState($lightId, $state)
    {
        $params = array(
            'token' => $this->accessToken,
        );
        
        $message = array(
            'bridgeId' => $this->bridgeId,
            'clipCommand' => array(
                'url' => '/api/0/lights/' . $lightId . '/state',
                'method' => 'PUT',
                'body' => $state,
            )
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
}
