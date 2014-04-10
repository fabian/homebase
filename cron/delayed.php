<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config.php';

set_time_limit(60);

$start = microtime(true);
$app = new Homebase\Application($config);

$i = 0;
do {

    $app['delayed']->run();

    $next = microtime(true) + 0.1;
    time_sleep_until($next);

} while ($next < $start + 60);
