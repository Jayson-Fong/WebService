<?php

use WS\App;

require __DIR__ . '/src/autoload.php';

$app = App::getInstance();
$app->router()->process($app->request()->getRoute())->display();