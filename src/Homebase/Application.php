<?php

namespace Homebase;

class Application extends \Silex\Application {

    public function __construct($config) {
        parent::__construct();

        $app = $this;

        // Silex
        $app['debug'] = $config['debug'];

        $app->register(new \Silex\Provider\DoctrineServiceProvider(), array(
            'dbs.options' => $config['database'],
        ));

        $app->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => __DIR__ . '/../../views',
        ));

        $app->register(new \Silex\Provider\UrlGeneratorServiceProvider());

        $app->register(new \Silex\Provider\ServiceControllerServiceProvider());

        // Homebase
        $app->register(new \Homebase\ServiceProvider($config));
        
        $app->mount('/', new \Homebase\ControllerProvider($config));

    }
}
