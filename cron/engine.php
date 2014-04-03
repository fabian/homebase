<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config.php';

$start = microtime(true);
set_time_limit(60);

$app = new Homebase\Application($config);
for ($i = 0; $i < 59; $i++) {

    $app['engine']->run();

    $next = $start + $i + 1;

    // if next in the past, try to catch up without sleep
    if ($next > microtime(true)) {

        time_sleep_until($start + $i + 1);
    }
}

