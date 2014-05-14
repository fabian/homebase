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

        $app['client.hue_remote'] = $app->share(function () use ($app, $config) {
            $client = new \Guzzle\Http\Client('https://www.meethue.com/api/');
            $client->setUserAgent('hue/1.1.3 CFNetwork/672.0.2 Darwin/14.0.0');
            return $client;
        });

        $app['hue_remote'] = $app->share(function () use ($app, $config) {
            return new \Homebase\Service\RemoteHue(
                $app['client.hue_remote'],
                $config['hue_remote']['bridge_id'],
                $config['hue_remote']['access_token']
            );
        });

        $app['client.hue_local'] = $app->share(function () use ($app, $config) {
            $client = new \Guzzle\Http\Client($config['hue_local']['url']);
            return $client;
        });

        $app['hue_local'] = $app->share(function () use ($app, $config) {
            return new \Homebase\Service\LocalHue(
                $app['client.hue_local']
            );
        });

        $app['oauth'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Service\OAuth(
                $app['db']
            );
        });

        $app['beacons'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Service\Beacons(
                $app['db']
            );
        });

        $app['engine'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Service\Engine(
                $app['beacons'],
                $app['lights'],
                $app['config']
            );
        });

        $app['delayed'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Service\Delayed(
                $app['lights'],
                $app[$config['hue']],
                $app['config']
            );
        });

        $app['sync'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Service\Sync(
                $app[$config['hue']],
                $app['lights']
            );
        });

        $app['config'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Service\Config(
                $app['db']
            );
        });

        $app['lights'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Service\Lights(
                $app['db']
            );
        });

        $app['dashboard.controller'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Controller\DashboardController(
                $app['twig'],
                $app['beacons'],
                $app['config'],
                $app['lights'],
                $app['url_generator']
            );
        });

        $app['report.controller'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Controller\ReportController(
                $app['twig'],
                $app['beacons'],
                $app['lights']
            );
        });

        $app['setup.controller'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Controller\SetupController(
                $app['twig'],
                $app['beacons'],
                $app['lights'],
                $app['url_generator']
            );
        });

        $app['oauth.controller'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Controller\OAuthController(
                $app['twig'],
                $app['oauth']
            );
        });

        $app['beacons.controller'] = $app->share(function() use ($app, $config) {
            return new \Homebase\Controller\BeaconsController(
                $app['beacons'],
                $app['oauth']
            );
        });
    }

    public function boot(\Silex\Application $app)
    {
    }
}
