<?php

namespace Homebase\Service;

use Guzzle\Tests\GuzzleTestCase;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Message\Response;

class LocalHueTest extends GuzzleTestCase
{
    protected function setUp()
    {
        $this->plugin = new MockPlugin();
        $this->client = new HttpClient();
        $this->client->addSubscriber($this->plugin);

        $this->hue = new LocalHue($this->client);
    }

    public function testSetLightState()
    {
        $this->plugin->addResponse(new Response(200, array(), '{}'));

        $data = $this->hue->setLightState('1', array('on' => true));

        $this->assertEquals(array(), $data);

        $requests = $this->plugin->getReceivedRequests();

        $this->assertEquals('PUT', $requests[0]->getMethod());
        $this->assertEquals('/lights/1/state', $requests[0]->getUrl());
        $this->assertEquals('{"on":true}', $requests[0]->getBody()->__toString());
    }

    public function testGetBridgeInfo()
    {
        $this->plugin->addResponse(new Response(200, array(), '{"lights":[]}'));

        $data = $this->hue->getBridgeInfo();

        $this->assertEquals(array('lights' => array()), $data);

        $requests = $this->plugin->getReceivedRequests();

        $this->assertEquals('GET', $requests[0]->getMethod());
        $this->assertEquals('', $requests[0]->getUrl());
    }
}
