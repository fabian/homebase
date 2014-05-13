<?php

namespace Homebase\Service;

class RedirectUriTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $uri = RedirectUri::create('http://example.org/', array('access_token' => 'ABC123'));

        $this->assertEquals('http://example.org/?access_token=ABC123', $uri);
    }

    public function testCreateParams()
    {
        $uri = RedirectUri::create('http://example.org/?foo=bar', array('access_token' => 'ABC123'));

        $this->assertEquals('http://example.org/?foo=bar&access_token=ABC123', $uri);
    }

    public function testCreateFragment()
    {
        $uri = RedirectUri::create('http://example.org/', array('access_token' => 'ABC123'), '#');

        $this->assertEquals('http://example.org/#access_token=ABC123', $uri);
    }

    public function testCreateFragmentParams()
    {
        $uri = RedirectUri::create('http://example.org/#foo=bar', array('access_token' => 'ABC123'), '#');

        $this->assertEquals('http://example.org/#foo=bar&access_token=ABC123', $uri);
    }

    public function testCreateError()
    {
        $this->assertEquals('http://example.org/?error=user_denied', RedirectUri::create('http://example.org/', array('error' => 'user_denied')));
    }
}
