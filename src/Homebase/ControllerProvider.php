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
        $controllers->get('/dashboard/logs/', 'dashboard.controller:logsAction');
        $controllers->get('/dashboard/beacons/', 'dashboard.controller:beaconsAction');
        $controllers->get('/report/', 'report.controller:indexAction')->bind('report');
        $controllers->get('/report/day/{day}/', 'report.controller:dayAction');
        $controllers->get('/setup/', 'setup.controller:indexAction')->bind('setup');
        $controllers->get('/beacons/{uuid}/{major}/{minor}', 'beacons.controller:getBeacon');
        $controllers->post('/beacons', 'beacons.controller:addBeacon');
        $controllers->post('/regions/', 'regions.controller:addRegion');

        return $controllers;
    }
}
