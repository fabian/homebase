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

        $app['regions'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Service\Regions(
                $app['db']
            );
        });

        $app['dashboard.controller'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Controller\DashboardController(
                $app['twig'],
                $app['log']
            );
        });

        $app['report.controller'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Controller\ReportController(
                $app['twig'],
                $app['log']
            );
        });

        $app['setup.controller'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Controller\SetupController(
                $app['twig']
            );
        });

        $app['beacons.controller'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Controller\BeaconsController(
                $app['beacons']
            );
        });

        $app['regions.controller'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Controller\RegionsController(
                $app['regions']
            );
        });
    }

    public function boot(\Silex\Application $app)
    {
    }
}
