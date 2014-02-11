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

        $app['client.remotehue'] = $app->share(function () use ($app) {
            $client = new \Guzzle\Http\Client('https://www.meethue.com/api/');
            $client->setUserAgent('hue/1.1.3 CFNetwork/672.0.2 Darwin/14.0.0');
            return $client;
        });

        $app['remotehue'] = $app->share(function () use ($app, $config) {
            return new \Homebase\Service\RemoteHue(
                $app['client.remotehue'],
                $config['remote_api']['bridge_id'],
                $config['remote_api']['access_token']
            );
        });

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

        $app['engine'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Service\Engine(
                $app['regions'],
                $app['remotehue']
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
