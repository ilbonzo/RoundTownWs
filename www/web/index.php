<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../config/prod.php';
$app = require_once __DIR__.'/../src/app.php';
$app->run();
