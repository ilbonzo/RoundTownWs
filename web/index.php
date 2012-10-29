<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->get('/', function() {
    return 'Hello Round Town!';
});

$app->get('/index', function() {
    return 'Hello Round Town! Index';
});

$app->run();
