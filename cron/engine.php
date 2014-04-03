<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config.php';

$start = microtime(true);
set_time_limit(60);

$app = new Homebase\Application($config);
$app['engine']->run();
exit;
for ($i = 0; $i < 59; $i++) {

    $app['engine']->run();

    time_sleep_until($start + $i + 1);
}

