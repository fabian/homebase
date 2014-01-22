<?php

namespace Homebase;

class ControllerProvider implements \Silex\ControllerProviderInterface
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function connect(\Silex\Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'dashboard.controller:indexAction')->bind('dashboard');
        $controllers->get('/dashboard/day', 'dashboard.controller:dayAction');
        $controllers->post('/beacons', 'beacons.controller:addAction');

        return $controllers;
    }
}
