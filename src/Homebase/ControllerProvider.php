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
        $controllers->post('/dashboard/mode/', 'dashboard.controller:modeAction')->bind('mode');
        $controllers->get('/dashboard/events/', 'dashboard.controller:eventsAction');
        $controllers->get('/dashboard/logs/', 'dashboard.controller:logsAction');
        $controllers->get('/dashboard/proximities/', 'dashboard.controller:proximitiesAction');
        $controllers->get('/report/', 'report.controller:indexAction')->bind('report');
        $controllers->get('/report/day/{day}/', 'report.controller:dayAction');
        $controllers->get('/report/measurements/', 'report.controller:measurementsAction');
        $controllers->get('/report/rooms/', 'report.controller:roomsAction');
        $controllers->get('/setup/', 'setup.controller:indexAction')->bind('setup');
        $controllers->post('/setup/', 'setup.controller:saveAction');
        $controllers->get('/setup/add/', 'setup.controller:addBeaconAction')->bind('addBeacon');
        $controllers->post('/setup/add/', 'setup.controller:addBeaconPostAction');
        $controllers->get('/setup/edit/{id}/', 'setup.controller:editBeaconAction')->bind('editBeacon');
        $controllers->post('/setup/edit/{id}/', 'setup.controller:editBeaconPostAction');
        $controllers->get('/oauth/authorize/', 'oauth.controller:authorizeAction')->bind('authorize');
        $controllers->post('/oauth/authorize/', 'oauth.controller:authorizePostAction');
        $controllers->get('/beacons/{uuid}/{major}/{minor}/', 'beacons.controller:getBeacon');
        $controllers->post('/api/beacons/state/', 'beacons.controller:addState');
        $controllers->post('/api/beacons/proximity/', 'beacons.controller:addProximity');

        return $controllers;
    }
}
