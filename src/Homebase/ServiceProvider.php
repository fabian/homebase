<?php

namespace Homebase;

class ServiceProvider implements \Silex\ServiceProviderInterface
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function register(\Silex\Application $app)
    {
        $config = $this->config;

        $app['log'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Service\Log(
                $app['db']
            );
        });

        $app['beacons'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Service\Beacons(
                $app['db']
            );
        });

        $app['dashboard.controller'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Controller\DashboardController(
                $app['twig'],
                $app['log']
            );
        });

        $app['beacons.controller'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Controller\BeaconsController(
                $app['beacons']
            );
        });
    }

    public function boot(\Silex\Application $app)
    {
    }
}
