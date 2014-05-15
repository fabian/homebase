<?php

namespace Homebase\Service;

use Guzzle\Tests\GuzzleTestCase;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Message\Response;

class RemoteHueTest extends GuzzleTestCase
{
    protected function setUp()
    {
        $this->plugin = new MockPlugin();
        $this->client = new HttpClient();
        $this->client->addSubscriber($this->plugin);

        $this->hue = new RemoteHue($this->client, '12345', '0fbe1ddfb');
    }

    public function testSetLightState()
    {
        $this->plugin->addResponse(new Response(200, array(), '{}'));

        $data = $this->hue->setLightState('1', array('on' => true));

        $this->assertEquals(array(), $data);

        $requests = $this->plugin->getReceivedRequests();

        $this->assertEquals('POST', $requests[0]->getMethod());
        $this->assertEquals('/sendmessage?token=0fbe1ddfb', $requests[0]->getUrl());
        $this->assertEquals('clipmessage=%7B%22bridgeId%22%3A%2212345%22%2C%22clipCommand%22%3A%7B%22url%22%3A%22%5C%2Fapi%5C%2F0%5C%2Flights%5C%2F1%5C%2Fstate%22%2C%22method%22%3A%22PUT%22%2C%22body%22%3A%7B%22on%22%3Atrue%7D%7D%7D', $requests[0]->getPostFields()->__toString());
    }

    public function testGetBridgeInfo()
    {
        $this->plugin->addResponse(new Response(200, array(), '{"lights":[]}'));

        $data = $this->hue->getBridgeInfo();

        $this->assertEquals(array('lights' => array()), $data);

        $requests = $this->plugin->getReceivedRequests();

        $this->assertEquals('GET', $requests[0]->getMethod());
        $this->assertEquals('/getbridge?token=0fbe1ddfb&bridgeid=12345', $requests[0]->getUrl());
    }
}
